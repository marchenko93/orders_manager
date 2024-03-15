<?php

namespace ordersList\models;

use ordersList\Module;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\data\Pagination;
use yii\db\Expression;
use yii\db\Query;
use yii\web\Response;
use yii2tech\csvgrid\CsvGrid;
use yii\web\BadRequestHttpException;

/**
 * OrdersList
 */
class OrdersList extends Model
{
    private const int ORDERS_PER_PAGE = 100;
    private const int SEARCH_TYPE_ORDER_ID = 1;
    private const int SEARCH_TYPE_LINK = 2;
    private const int SEARCH_TYPE_USERNAME = 3;

    public ?string $status = null;
    public ?string $mode = null;
    public ?string $search = null;
    public ?int $searchType = null;
    public ?int $serviceId = null;
    public Pagination $pagination;
    public array $filters;
    private ?array $searchUserIds = null;

    /**
     * @return string[]
     */
    public function attributes(): array
    {
        return ['status', 'mode', 'search', 'searchType', 'serviceId'];
    }

    /**
     * @return void
     */
    public function init(): void
    {
        $this->setFilters();
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            [['status', 'mode', 'search', 'searchType', 'serviceId'], 'default', 'value' => null],
            ['status', 'in', 'range' => $this->getValidStatuses()],
            ['mode', 'in', 'range' => $this->getValidModes()],
            ['search', 'string'],
            ['searchType', 'in', 'range' => $this->getValidSearchTypes()],
            ['serviceId', 'integer']
        ];
    }

    /**
     * @return string
     */
    public function formName(): string
    {
        return '';
    }

    /**
     * @return array[]
     */
    public function getColumnsToDisplay(): array
    {
        return [
            [
                'attribute' => 'id',
                'label' => Module::t('list', 'ID')
            ],
            [
                'attribute' => 'username',
                'label' => Module::t('list', 'User')
            ],
            [
                'attribute' => 'link',
                'label' => Module::t('list', 'Link')
            ],
            [
                'attribute' => 'quantity',
                'label' => Module::t('list', 'Quantity')
            ],
            [
                'attribute' => 'service',
                'label' => Module::t('list', 'Service')
            ],
            [
                'attribute' => 'status',
                'label' => Module::t('list', 'Status')
            ],
            [
                'attribute' => 'mode',
                'label' => Module::t('list', 'Mode')
            ],
            [
                'attribute' => 'created_at',
                'label' => Module::t('list', 'Created')
            ],
        ];
    }

    /**
     * @return array
     * @throws BadRequestHttpException
     */
    public function getOrders(): array
    {
        $this->validateInputParams();
        $this->prepareFiltersToDisplay();
        $this->setPagination();
        $ordersQuery = $this->getOrdersQuery();
        $ordersQuery
            ->select($this->getColumnsToSelect())
            ->offset($this->pagination->offset)
            ->limit($this->pagination->limit)
        ;
        return $ordersQuery->all();
    }

    /**
     * @return Response
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     */
    public function exportOrders(): Response
    {
        $this->validateInputParams();
        $query = $this->getOrdersQuery();
        $columnsToSelect = $this->getColumnsToSelect();
        $columnsToSelect['service'] = $this->getServiceExpression();
        $query->select($columnsToSelect);
        $exporter = new CsvGrid([
            'dataProvider' => new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => self::ORDERS_PER_PAGE,
                ],
            ]),
            'columns' => $this->getColumnsToDisplay(),
        ]);
        return $exporter->export()->send('orders.csv');
    }

    /**
     * @return int
     */
    private function getTotalOrdersNumber(): int
    {
        if (!$this->serviceId) {
            return $this->getTotalOrdersNumberWithoutServiceFilter();
        }
        return $this->filters['service']['services'][$this->serviceId]['orders_number'];
    }

    /**
     * @return array
     */
    private function getColumnsToSelect(): array
    {
        return [
            'o.id',
            'username' => 'CONCAT(u.first_name, " ", u.last_name)',
            'o.link',
            'o.quantity',
            'o.service_id',
            'service' => 's.name',
            'status' => $this->getStatusExpression(),
            'mode' => $this->getModeExpression(),
            'created_at' => 'FROM_UNIXTIME(o.created_at, "%Y-%m-%d %h:%i:%s")',
        ];
    }

    /**
     * @return Query
     */
    private function getOrdersQuery(): Query
    {
        $query = (new Query())
            ->from('orders o')
            ->innerJoin('users u', 'o.user_id = u.id')
            ->innerJoin('services s', 'o.service_id = s.id')
            ->orderBy(['o.id' => SORT_DESC])
        ;
        $this->addFiltersToQuery($query);
        return $query;
    }

    /**
     * @param Query $query
     * @param bool $isServiceFilterEnabled
     * @return void
     */
    private function addFiltersToQuery(Query $query, bool $isServiceFilterEnabled = true): void
    {
        if (!is_null($this->status)) {
            $query->andWhere('o.status=:status', [':status' => $this->getStatusCode()]);
        }
        if (!is_null($this->mode)) {
            $query->andWhere('o.mode=:mode', [':mode' => $this->getModeCode()]);
        }
        if (!is_null($this->serviceId) && $isServiceFilterEnabled) {
            $query->andWhere('s.id=:id', [':id' => $this->serviceId]);
        }
        if (!is_null($this->searchType) && $this->search) {
            if (self::SEARCH_TYPE_ORDER_ID == $this->searchType) {
                $query->andWhere(['=', 'o.id', $this->search]);
            } elseif (self::SEARCH_TYPE_USERNAME == $this->searchType) {
                $query->andWhere(['in', 'u.id', $this->searchUserIds]);
            } elseif (self::SEARCH_TYPE_LINK == $this->searchType) {
                $query->andWhere(['like', 'o.link', $this->search]);
            }
        }
    }

    /**
     * @return void
     */
    private function setSearchUserIds(): void
    {
        $nameWords = explode(' ', preg_replace('/\s+/', ' ', trim($this->search)));
        $firstWord = $nameWords[0];
        $secondWord = $nameWords[1] ?? false;
        $firstNameIdsQuery = (new Query())
            ->select('id')
            ->from('users')
            ->where('first_name = :first_name', [':first_name' => $firstWord])
        ;
        $lastNameIdsQuery = (new Query())
            ->select('id')
            ->from('users')
            ->where('last_name = :last_name', [':last_name' => $firstWord])
        ;

        if ($secondWord) {
            $firstNameIdsQuery->andWhere('last_name = :last_name', [':last_name' => $secondWord]);
            $lastNameIdsQuery->andWhere('first_name = :first_name', [':first_name' => $secondWord]);
        }

        $firstNameIds = $firstNameIdsQuery->column();
        $lastNameIds = $lastNameIdsQuery->column();
        $this->searchUserIds = array_merge($firstNameIds, $lastNameIds);
    }

    /**
     * @return bool
     */
    private function isServiceIdValid(): bool
    {
        if ($this->serviceId && !array_key_exists($this->serviceId, $this->filters['service']['services'])) {
            return false;
        }
        return true;
    }

    /**
     * @return Expression
     */
    private function getModeExpression(): Expression
    {
        $sqlExpression = 'CASE o.mode ';
        foreach ($this->filters['mode']['labels'] as $code => $label) {
            $sqlExpression .= 'WHEN ' . $code . ' THEN "' . $label . '" ';
        }
        $sqlExpression .= 'END';
        return new Expression($sqlExpression);
    }

    /**
     * @return Expression
     */
    private function getStatusExpression(): Expression
    {
        $sqlExpression = 'CASE o.status ';
        foreach ($this->filters['status']['labels'] as $code => $label) {
            $sqlExpression .= 'WHEN ' . $code . ' THEN "' . $label . '" ';
        }
        $sqlExpression .= 'END';
        return new Expression($sqlExpression);
    }

    /**
     * @return Expression
     */
    private function getServiceExpression(): Expression
    {
        $sqlExpression = 'CASE o.service_id ';
        foreach ($this->filters['service']['services'] as $serviceId => $service) {
            $sqlExpression .= 'WHEN ' . $serviceId . ' THEN "' . $service['orders_number'] . ' ' . $service['name'] . '" ';
        }
        $sqlExpression .= 'END';
        return new Expression($sqlExpression);
    }

    /**
     * @return void
     */
    private function setFilters(): void
    {
        $this->filters = [
            'status' => [
                'values' => [
                    Order::STATUS_CODE_PENDING => 'pending',
                    Order::STATUS_CODE_IN_PROGRESS => 'inprogress',
                    Order::STATUS_CODE_COMPLETED => 'completed',
                    Order::STATUS_CODE_CANCELED => 'canceled',
                    Order::STATUS_CODE_ERROR => 'error',
                ],
                'labels' => [
                    Order::STATUS_CODE_PENDING => Module::t('list', 'Pending'),
                    Order::STATUS_CODE_IN_PROGRESS => Module::t('list', 'In progress'),
                    Order::STATUS_CODE_COMPLETED => Module::t('list', 'Completed'),
                    Order::STATUS_CODE_CANCELED => Module::t('list', 'Canceled'),
                    Order::STATUS_CODE_ERROR => Module::t('list', 'Error'),
                ]
            ],
            'mode' => [
                'values' => [
                    Order::MODE_CODE_MANUAL => 'manual',
                    Order::MODE_CODE_AUTO => 'auto',
                ],
                'labels' => [
                    Order::MODE_CODE_MANUAL => Module::t('list', 'Manual'),
                    Order::MODE_CODE_AUTO => Module::t('list', 'Auto'),
                ],
            ],
            'searchType' => [
                'labels' => [
                    self::SEARCH_TYPE_ORDER_ID => Module::t('list', 'Order ID'),
                    self::SEARCH_TYPE_LINK => Module::t('list', 'Link'),
                    self::SEARCH_TYPE_USERNAME => Module::t('list', 'Username'),
                ],
            ],
        ];
    }

    /**
     * @return void
     */
    private function addServiceFilter(): void
    {
        $serviceOrdersQuery = (new Query())
            ->select([
                'o.service_id',
                'orders_number' => 'COUNT(*)',
            ])
            ->from('orders o')
            ->innerJoin('users u', 'o.user_id = u.id')
            ->groupBy('o.service_id')
        ;
        $this->addFiltersToQuery($serviceOrdersQuery, false);

        $query = (new Query())
            ->select([
                's.id',
                's.name',
                'orders_number' => 'IF(service_orders.orders_number IS NULL, 0, service_orders.orders_number)',
            ])
            ->from('services s')
            ->leftJoin(['service_orders' => $serviceOrdersQuery], 's.id = service_orders.service_id')
            ->orderBy(['orders_number' => SORT_DESC])
        ;

        $this->filters['service']['services'] = $query->indexBy('id')->all();
    }

    /**
     * @return array
     */
    private function getValidStatuses(): array
    {
        return $this->filters['status']['values'];
    }

    /**
     * @return array
     */
    private function getValidModes(): array
    {
        return $this->filters['mode']['values'];
    }

    /**
     * @return array
     */
    private function getValidSearchTypes(): array
    {
        return array_keys($this->filters['searchType']['labels']);
    }

    /**
     * @return int|false
     */
    private function getStatusCode(): int|false
    {
        return array_search($this->status, $this->filters['status']['values']);
    }

    /**
     * @return int|false
     */
    private function getModeCode(): int|false
    {
        return array_search($this->mode, $this->filters['mode']['values']);
    }

    /**
     * @return void
     */
    private function setPagination(): void
    {
        $this->pagination = new Pagination([
            'pageSizeLimit' => [1, self::ORDERS_PER_PAGE],
            'defaultPageSize' => self::ORDERS_PER_PAGE,
            'totalCount' => $this->getTotalOrdersNumber(),
        ]);
    }

    /**
     * @return void
     * @throws BadRequestHttpException
     */
    private function validateInputParams(): void
    {
        if (!$this->validate()) {
            throw new BadRequestHttpException(implode(' ', $this->getErrorSummary(true)));
        }
        if (self::SEARCH_TYPE_USERNAME === $this->searchType) {
            $this->setSearchUserIds();
        }
        $this->addServiceFilter();
        if (!$this->isServiceIdValid()) {
            throw new BadRequestHttpException('Invalid service ID.');
        }
    }

    /**
     * @return void
     */
    private function prepareFiltersToDisplay(): void
    {
        $this->filters['status']['selectedValue'] = $this->status;
        $this->filters['mode']['selectedValue'] = $this->mode;
        $this->filters['searchType']['selectedValue'] = $this->searchType;
        $this->filters['service']['selectedValue'] = $this->serviceId;
        $this->filters['service']['totalOrdersNumber'] = $this->getTotalOrdersNumberWithoutServiceFilter();
    }

    /**
     * @return int
     */
    private function getTotalOrdersNumberWithoutServiceFilter(): int
    {
        return array_reduce(
            $this->filters['service']['services'],
            function ($totalOrdersNumber, $service) {
                return $totalOrdersNumber + $service['orders_number'];
            },
            0
        );
    }
}

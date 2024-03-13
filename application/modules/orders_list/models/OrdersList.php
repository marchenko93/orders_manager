<?php

namespace app\modules\orders_list\models;

use app\modules\orders_list\Module;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\db\Query;
use yii\web\BadRequestHttpException;
use yii2tech\csvgrid\CsvGrid;

class OrdersList extends Model
{
    public const int ORDERS_PER_PAGE = 100;
    private const int SEARCH_TYPE_ORDER_ID = 1;
    private const int SEARCH_TYPE_LINK = 2;
    private const int SEARCH_TYPE_USERNAME = 3;

    public ?string $status;
    public ?string $mode;
    public ?string $search;
    public ?int $searchType;
    public ?int $serviceId;
    private array $statuses;
    private array $modes;
    private array $searchTypes;
    private array $columns;
    private array $services;
    private array $ordersQuerySelectColumns;
    private ?array $searchUserIds = null;

    public function __construct(
        array $config = [],
        ?string $status = null,
        ?string $mode = null,
        ?string $search = null,
        ?int $searchType = null,
        ?int $serviceId = null
    ) {
        parent::__construct($config);
        $this->status = $status;
        $this->mode = $mode;
        $this->search = $search;
        $this->searchType = $searchType;
        $this->serviceId = $serviceId;
        if (!$this->validate()) {
            throw new BadRequestHttpException(implode('.', $this->errors));
        }
        if (self::SEARCH_TYPE_USERNAME === $searchType) {
            $this->loadSearchUserIdsFromDb();
        }
        $this->loadServicesFromDatabase();
        if ($this->serviceId && !array_key_exists($this->serviceId, $this->services)) {
            throw new BadRequestHttpException('Invalid service ID.');
        }
        $this->ordersQuerySelectColumns = [
            'o.id',
            'username' => 'CONCAT(u.first_name, " ", u.last_name)',
            'o.link',
            'o.quantity',
            'o.service_id',
            'service_name' => 's.name',
            'status' => $this->getStatusExpression(),
            'mode' => $this->getModeExpression(),
            'created_at' => 'FROM_UNIXTIME(o.created_at, "%Y-%m-%d %h:%i:%s")',
        ];
    }

    public function rules(): array
    {
        return [
            [['status', 'mode', 'search', 'searchType'], 'default', 'value' => null],
            ['status', 'in', 'range' => array_map(fn ($status) => $status['status'], $this->statuses)],
            ['mode', 'in', 'range' => array_map(fn ($mode) => $mode['mode'], $this->modes)],
            ['search', 'string'],
            ['searchType', 'in', 'range' => array_keys($this->searchTypes)],
        ];
    }

    public function getStatuses(): array
    {
        return $this->statuses;
    }

    public function getModes(): array
    {
        return $this->modes;
    }

    public function getSearchTypes(): array
    {
        return $this->searchTypes;
    }

    public function getServices(): array
    {
        return $this->services;
    }

    public function getColumns(): array
    {
        return $this->columns;
    }

    public function getOrdersQuery(): Query
    {
        $query = (new Query())
            ->select($this->ordersQuerySelectColumns)
            ->from('orders o')
            ->innerJoin('users u', 'o.user_id = u.id')
            ->innerJoin('services s', 'o.service_id = s.id')
            ->orderBy(['o.id' => SORT_DESC])
        ;
        $this->addFiltersToQuery($query);
        return $query;
    }

    public function getOrdersNumberForAllServices(): int
    {
        return array_reduce(
            $this->services,
            function ($totalOrdersNumber, $service) {
                return $totalOrdersNumber + $service['orders_number'];
            },
            0
        );
    }

    public function getOrdersNumberForSelectedService(): int
    {
        if (!$this->serviceId) {
            return $this->getOrdersNumberForAllServices();
        }
        return $this->services[$this->serviceId]['orders_number'];
    }

    public function exportQueryResultToCsv(Query $query): void
    {
        $selectColumns = array_merge(
            $this->ordersQuerySelectColumns,
            ['service_orders_number' => $this->getServiceExpression()]
        );
        $query->select($selectColumns);
        $exporter = new CsvGrid([
            'dataProvider' => new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => static::ORDERS_PER_PAGE,
                ],
            ]),
            'columns' => $this->columns,
        ]);
        $exporter->export()->send('orders.csv');
    }

    public function init(): void
    {
        $this->statuses = [
            Order::STATUS_CODE_PENDING => [
                'status' => 'pending',
                'label' => Module::t('list', 'Pending')
            ],
            Order::STATUS_CODE_IN_PROGRESS => [
                'status' => 'inprogress',
                'label' => Module::t('list', 'In progress')
            ],
            Order::STATUS_CODE_COMPLETED => [
                'status' => 'completed',
                'label' => Module::t('list', 'Completed')
            ],
            Order::STATUS_CODE_CANCELED => [
                'status' => 'canceled',
                'label' => Module::t('list', 'Canceled')
            ],
            Order::STATUS_CODE_ERROR => [
                'status' => 'error',
                'label' => Module::t('list', 'Error')
            ],
        ];

        $this->modes = [
            Order::MODE_CODE_MANUAL => [
                'mode' => 'manual',
                'label' => Module::t('list', 'Manual')
            ],
            Order::MODE_CODE_AUTO => [
                'mode' => 'auto',
                'label' => Module::t('list', 'Auto')
            ],
        ];

        $this->searchTypes = [
            self::SEARCH_TYPE_ORDER_ID => [
                'select_expression' => 'o.id',
                'label' => Module::t('list', 'Order ID')
            ],
            self::SEARCH_TYPE_LINK => [
                'select_expression' => 'o.link',
                'label' => Module::t('list', 'Link')
            ],
            self::SEARCH_TYPE_USERNAME => [
                'select_expression' => 'CONCAT(u.first_name, " ", u.last_name)',
                'label' => Module::t('list', 'Username')
            ],
        ];

        $this->columns = [
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
                'attribute' => 'service_name',
                'label' => Module::t('list', 'Service')
            ],
            [
                'attribute' => 'service_orders_number',
                'label' => Module::t('list', 'Service orders number')
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

    private function loadServicesFromDatabase(): void
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
            ->orderBy(['orders_number' => SORT_DESC]);

        $this->services = $query->indexBy('id')->all();
    }

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
            if (static::SEARCH_TYPE_ORDER_ID == $this->searchType) {
                $query->andWhere(['=', 'o.id', $this->search]);
            } elseif (static::SEARCH_TYPE_USERNAME == $this->searchType) {
                $query->andWhere(['in', 'u.id', $this->searchUserIds]);
            } elseif (static::SEARCH_TYPE_LINK == $this->searchType) {
                $query->andWhere(['like', 'o.link', $this->search]);
            }
        }
    }

    private function getStatusCode(): ?int
    {
        foreach ($this->statuses as $code => $status) {
            if ($this->status === $status['status']) {
                return $code;
            }
        }
        return null;
    }

    private function getModeCode(): ?int
    {
        foreach ($this->modes as $code => $mode) {
            if ($this->mode === $mode['mode']) {
                return $code;
            }
        }
        return null;
    }

    private function getModeExpression(): Expression
    {
        $sqlExpression = 'CASE o.mode ';
        foreach ($this->modes as $code => $mode) {
            $sqlExpression .= 'WHEN ' . $code . ' THEN "'. $mode['label'] . '" ';
        }
        $sqlExpression .= 'END';
        return new Expression($sqlExpression);
    }

    private function getStatusExpression(): Expression
    {
        $sqlExpression = 'CASE o.status ';
        foreach ($this->statuses as $code => $status) {
            $sqlExpression .= 'WHEN ' . $code . ' THEN "'. $status['label'] . '" ';
        }
        $sqlExpression .= 'END';
        return new Expression($sqlExpression);
    }

    private function getServiceExpression(): Expression
    {
        $sqlExpression = 'CASE o.service_id ';
        foreach ($this->services as $serviceId => $service) {
            $sqlExpression .= 'WHEN ' . $serviceId . ' THEN "'. $service['orders_number'] . '" ';
        }
        $sqlExpression .= 'END';
        return new Expression($sqlExpression);
    }

    private function loadSearchUserIdsFromDb(): void
    {
        $nameWords = explode(' ', preg_replace('/\s+/', ' ', trim($this->search)));
        $firstWord = $nameWords[0];
        $secondWord = $nameWords[1] ?? false;
        $firstNameIdsQuery =(new Query())
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
}

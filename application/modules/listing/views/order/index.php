<nav class="navbar navbar-fixed-top navbar-default">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-navbar-collapse">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
        </div>
        <div class="collapse navbar-collapse" id="bs-navbar-collapse">
            <ul class="nav navbar-nav">
                <li class="active"><a href="#">Orders</a></li>
            </ul>
        </div>
    </div>
</nav>
<div class="container-fluid">
    <ul class="nav nav-tabs p-b">
        <li class="active"><a href="#">All orders</a></li>
        <li><a href="#">Pending</a></li>
        <li><a href="#">In progress</a></li>
        <li><a href="#">Completed</a></li>
        <li><a href="#">Canceled</a></li>
        <li><a href="#">Error</a></li>
        <li class="pull-right custom-search">
            <form class="form-inline" action="/admin/orders" method="get">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" value="" placeholder="Search orders">
                    <span class="input-group-btn search-select-wrap">

            <select class="form-control search-select" name="search-type">
              <option value="1" selected="">Order ID</option>
              <option value="2">Link</option>
              <option value="3">Username</option>
            </select>
            <button type="submit" class="btn btn-default"><span class="glyphicon glyphicon-search" aria-hidden="true"></span></button>
            </span>
                </div>
            </form>
        </li>
    </ul>
    <table class="table order-table">
        <thead>
        <tr>
            <th>ID</th>
            <th>User</th>
            <th>Link</th>
            <th>Quantity</th>
            <th class="dropdown-th">
                <div class="dropdown">
                    <button class="btn btn-th btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                        Service
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
                        <li class="active"><a href="">All (894931)</a></li>
                        <li><a href=""><span class="label-id">214</span>  Real Views</a></li>
                        <li><a href=""><span class="label-id">215</span> Page Likes</a></li>
                        <li><a href=""><span class="label-id">10</span> Page Likes</a></li>
                        <li><a href=""><span class="label-id">217</span> Page Likes</a></li>
                        <li><a href=""><span class="label-id">221</span> Followers</a></li>
                        <li><a href=""><span class="label-id">224</span> Groups Join</a></li>
                        <li><a href=""><span class="label-id">230</span> Website Likes</a></li>
                    </ul>
                </div>
            </th>
            <th>Status</th>
            <th class="dropdown-th">
                <div class="dropdown">
                    <button class="btn btn-th btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                        Mode
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
                        <li class="active"><a href="">All</a></li>
                        <li><a href="">Manual</a></li>
                        <li><a href="">Auto</a></li>
                    </ul>
                </div>
            </th>
            <th>Created</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>558931</td>
            <td>waliullah</td>
            <td class="link">/p/BMRSv4FDevy/</td>
            <td>3000</td>
            <td class="service">
                <span class="label-id">213</span>Likes
            </td>
            <td>Pending</td>
            <td>Manual</td>
            <td><span class="nowrap">2016-01-27</span><span class="nowrap">15:13:52</span></td>
        </tr>
        <tr>
            <td>55892</td>
            <td>spiderfady</td>
            <td class="link">/followers</td>
            <td>1800</td>
            <td class="service">
                <span class="label-id">3</span> Real Views
            </td>
            <td>Canceled</td>
            <td>Auto</td>
            <td><span class="nowrap">2016-01-27</span><span class="nowrap">15:13:52</span></td>
        </tr>
        <tr>
            <td>55891</td>
            <td>spiderfady</td>
            <td class="link">/com.usk</td>
            <td>1800</td>
            <td class="service">
                <span class="label-id">15</span> Views
            </td>
            <td>Canceled</td>
            <td>Auto</td>
            <td><span class="nowrap">2016-01-27</span><span class="nowrap">15:13:52</span></td>
        </tr>
        <tr>
            <td>52137</td>
            <td>gulaka</td>
            <td class="link">/p/BMD5RzxgRke/</td>
            <td>1800</td>
            <td class="service">
                <span class="label-id">5</span> Comment
            </td>
            <td>Error</td>
            <td>Auto</td>
            <td><span class="nowrap">2016-01-27</span><span class="nowrap">15:13:52</span></td>
        </tr>
        </tbody>
    </table>
    <div class="row">
        <div class="col-sm-8">

            <nav>
                <ul class="pagination">
                    <li class="disabled"><a href="" aria-label="Previous">&laquo;</a></li>
                    <li class="active"><a href="">1</a></li>
                    <li><a href="">2</a></li>
                    <li><a href="">3</a></li>
                    <li><a href="">4</a></li>
                    <li><a href="">5</a></li>
                    <li><a href="">6</a></li>
                    <li><a href="">7</a></li>
                    <li><a href="">8</a></li>
                    <li><a href="">9</a></li>
                    <li><a href="">10</a></li>
                    <li><a href="" aria-label="Next">&raquo;</a></li>
                </ul>
            </nav>

        </div>
        <div class="col-sm-4 pagination-counters">
            1 to 100 of 3263
        </div>

    </div>
</div>

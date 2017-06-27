<?php include('./header.php') ?>
<?php include('./nav.php') ?>
<div class="container theme-showcase" role="main">
    <div class="row">
        <div class="col-md-12">
            <form class="form-inline">
                <div class="form-group">
                    <label>时间范围：</label>
                    <input type="text" class="form-control" placeholder="开始时间" onclick="laydate({ istime: true, format: 'YYYY-MM-DD hh:mm:ss'})">
                    <input type="text" class="form-control" placeholder="结束时间" onclick="laydate({ istime: true, format: 'YYYY-MM-DD hh:mm:ss'})">
                </div>
                <div class="form-group col-md-offset-1">
                    <label for="exampleInputEmail2">请求地址：</label>
                    <input type="email" class="form-control" id="exampleInputEmail2" placeholder="url">
                </div>
                <button type="submit" class="btn btn-default">搜索</button>
            </form>
        </div>
    </div>
    <div class="page-header">
        <h1>最近访问</h1>
    </div>
    <div class="row">
        <div class="col-md-12">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>时间</th>
                    <th>URL</th>
                    <th>状态码</th>
                    <th>响应(ms)</th>
                    <th>Server</th>
                </tr>
                </thead>
                <tbody>
                <tr class="danger">
                    <td>2017-06-26 11:11:11</td>
                    <td><span class="label label-primary">POST</span> http://mllphp.com/goods/Index/index</td>
                    <td>200</td>
                    <td><span class="label label-danger">800.3232</span></td>
                    <td>192.168.1.1</td>
                </tr>
                <tr>
                    <td>2017-06-26 11:11:11</td>
                    <td>
                        <span class="label label-info">GET</span>
                        <a href="./p">http://mllphp.com/goods/Index/index</a>
                    </td>
                    <td>200</td>
                    <td><span class="label label-success">40.3232</span></td>
                    <td>192.168.1.1</td>
                </tr>
                <tr>
                    <td>2017-06-26 11:11:11</td>
                    <td>http://mllphp.com/goods/Index/index</td>
                    <td>200</td>
                    <td>40.3232</td>
                    <td>192.168.1.1</td>
                </tr>
                </tbody>
            </table>
            <nav aria-label="Page navigation" class="pull-right">
                <ul class="pagination">
                    <li>
                        <a href="#" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    <li><a href="#">1</a></li>
                    <li><a href="#">2</a></li>
                    <li><a href="#">3</a></li>
                    <li><a href="#">4</a></li>
                    <li><a href="#">5</a></li>
                    <li>
                        <a href="#" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</div> <!-- /container -->
<?php include('./footer.php') ?>

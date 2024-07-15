{include common/header@phpomg/admin}
<script>
    function search(params) {
        document.getElementById('search').value = params.q;
        document.getElementById('items').innerHTML = '<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>';
        $.ajax({
            type: "GET",
            url: "{echo $router->build('/ebcms/store/query')}",
            data: {
                api: 'search',
                params: params
            },
            dataType: "JSON",
            success: function(response) {
                if (response.status) {
                    var html = '';
                    var urlbase = "{echo $router->build('/ebcms/store/item')}";
                    response.data.items.forEach(item => {
                        html += '<div class="d-flex gap-3 position-relative">';
                        html += '    <div>';
                        html += '        <a class="text-decoration-none stretched-link fw-bold" href="' + urlbase + '?name=' + item.name + '"><img class="img-thumbnail" width="100" src="' + item.logo + '"></a>';
                        html += '    </div>';
                        html += '    <div class="d-flex flex-column gap-2 flex-grow-1 bg-light p-3">';
                        html += '        <div><span class="fs-6 fw-bold">' + item.title + '</span><sup class="ms-1 text-secondary">' + item.version + '</sup></div>';
                        html += '        <div class="text-muted text-wrap">' + item.description + '</div>';
                        html += '        <div><code>' + item.name + '</code> </div>';
                        html += '    </div>';
                        html += '</div>';
                    });
                    document.getElementById('items').innerHTML = html;
                } else {
                    document.getElementById('items').innerHTML = response.message;
                }
            },
            error: function() {
                document.getElementById('items').innerHTML = '发生错误，请稍后再试';
            }
        });
    }
</script>
<div class="h1 my-4">应用商店</div>
<div class="my-3">
    <input type="search" class="form-control" placeholder="搜索：请输入关键词" style="width:300px;" id="search" oninput="search({q:this.value})">
    <div class="form-text mt-3">
        <a class="bg-primary rounded-pill text-white py-1 px-2 text-decoration-none" href="javascript:search({q:'我的'});">我的</a>
        <a class="bg-danger rounded-pill text-white py-1 px-2 text-decoration-none" href="javascript:search({q:'可升级'});">可升级</a>
        <a class="bg-warning rounded-pill text-white py-1 px-2 text-decoration-none" href="javascript:search({q:'随机'});">随机</a>
        <a class="bg-success rounded-pill text-white py-1 px-2 text-decoration-none" href="javascript:search({q:'推荐'});">推荐</a>
        <a class="bg-info rounded-pill text-white py-1 px-2 text-decoration-none" href="javascript:search({q:'近期上架'});">近期上架</a>
        <a class="bg-secondary rounded-pill text-white py-1 px-2 text-decoration-none" href="javascript:search({q:'近期更新'});">近期更新</a>
    </div>
</div>
<script>
    $(function() {
        search({
            q: ''
        });
    });
</script>
<div id="items" class="d-flex flex-column gap-4"></div>
{include common/footer@phpomg/admin}
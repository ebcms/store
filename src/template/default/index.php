{include common/header@ebcms/admin}
<script>
    var installed = <?php echo json_encode($installed); ?>;

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
                if (response.code == 0) {
                    var html = '';
                    var urlbase = "{echo $router->build('/ebcms/store/item')}";
                    response.data.items.forEach(package => {
                        html += '<div class="position-relative border rounded item d-flex p-2" style="width:400px;">';
                        html += '    <div>';
                        html += '        <img style="cursor:pointer;height:100px;width:100px;" src="' + package.extra.icon + '">';
                        html += '    </div>';
                        html += '    <div class="ms-4">';
                        html += '        <div class="mt-0 mb-1"><strong><a class="text-decoration-none stretched-link" href="' + urlbase + '?name=' + package.name + '">' + package.title + '</a></strong><sup class="text-muted ms-1">' + package.version + '</sup></div>';
                        html += '        <div class="text-muted text-wrap">' + package.description + '</div>';
                        html += '    </div>';
                        html += '    <div class="position-absolute top-0 end-0 p-2">';
                        if (installed.hasOwnProperty(package.name) && (installed[package.name] != package.version)) {
                            html += '        <div class="text-danger fw-bold"><small>[可升级]<small></div>';
                        }
                        html += '    </div>';
                        html += '</div>';
                    });
                    document.getElementById('items').innerHTML = html;
                }
            }
        });
    }
</script>
<div class="container">
    <div class="h1 my-4">应用商店</div>
    <style>
        .item:hover {
            background-color: #ffffbb;
        }
    </style>
    <div class="my-3">
        <input type="text" class="form-control" placeholder="搜索：请输入关键词" style="width:300px;" id="search" oninput="search({q:this.value})">
        <div class="form-text mt-3">
            <a class="bg-success rounded-pill text-white py-1 px-2 text-decoration-none" href="javascript:search({q:'推荐'});">推荐</a>
            <a class="bg-danger rounded-pill text-white py-1 px-2 text-decoration-none" href="javascript:search({q:'可升级'});">可升级</a>
            <a class="bg-primary rounded-pill text-white py-1 px-2 text-decoration-none" href="javascript:search({q:'已购买'});">已购买</a>
            <a class="bg-info rounded-pill text-white py-1 px-2 text-decoration-none" href="javascript:search({q:'近期上架'});">近期上架</a>
            <a class="bg-secondary rounded-pill text-white py-1 px-2 text-decoration-none" href="javascript:search({q:'近期更新'});">近期更新</a>
            <!-- <a class="bg-secondary rounded-pill text-white py-1 px-2 text-decoration-none" href="javascript:search({q:'免费'});">免费</a> -->
        </div>
    </div>
    <script>
        $(function() {
            search({
                q: ''
            });
        });
    </script>
    <div id="items" class="d-flex justify-content-start gap-3 flex-wrap">
    </div>
</div>
{include common/header@ebcms/admin}
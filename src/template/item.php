{include common/header@php94/admin}
<div class="h1 my-4">应用商店</div>
<div class="my-4 d-flex flex-column gap-4">
    <div class="d-flex gap-3">
        <div>
            <img src="{echo $app['logo']}" class="img-thumbnail" width="100" alt="">
        </div>
        <div class="d-flex flex-column gap-2 flex-grow-1 bg-light p-3">
            <div><span class="fs-6 fw-bold">{$app['title']?:'-'}</span><sup class="ms-1 text-secondary">{$app['version']??''}</sup></div>
            <div>{$app.description}</div>
            <div><code>{$app.name}</code> </div>
            <div>
                <a href="{$app['url']??''}" target="_blank">详细介绍&gt;</a>
            </div>
        </div>
    </div>
    <div>
        {if $type=='install'}
        <button class="btn btn-primary" onclick="EBCMS.handler();" id="handler">一键安装</button>
        {else}
        <button class="btn btn-primary" onclick="EBCMS.handler();" id="handler">一件升级</button>
        {/if}
    </div>
    <div class="console p-3 text-white bg-dark overflow-auto" style="height: 300px;"></div>
</div>
<script>
    var EBCMS = {};
    $(function() {
        EBCMS.state = 0;
        EBCMS.stop = function(message) {
            EBCMS.console(message, 'red');
            EBCMS.console("完毕<hr>");
            EBCMS.state = 0;
            $("#handler").removeClass('btn-warning').addClass('btn-primary').html('一键安装');
        };
        EBCMS.handler = function() {
            if (EBCMS.state) {
                EBCMS.state = 0;
                EBCMS.console("正在终止...", 'red');
            } else {
                if (confirm('此操作可能发生意外风险，请先手动备份系统，继续吗？')) {
                    EBCMS.state = 1;
                    $("#handler").removeClass('btn-primary').addClass('btn-warning').html('一键终止');
                    EBCMS.check();
                }
            }
        }
        EBCMS.check = function() {
            EBCMS.console("版本检测...");
            $.ajax({
                type: "GET",
                url: "{echo $router->build('/ebcms/store/check')}",
                data: {
                    name: "{$app.name}",
                },
                dataType: "json",
                success: function(response) {
                    if (response.error) {
                        EBCMS.stop(response.message);
                    } else {
                        if (!EBCMS.state) {
                            EBCMS.stop('已终止(检测完毕)');
                            return;
                        }
                        EBCMS.console(response.message);
                        EBCMS.source();
                    }
                },
                error: function(context) {
                    EBCMS.stop("发生错误：" + context.statusText);
                }
            });
        };
        EBCMS.source = function() {
            EBCMS.console("获取资源信息...");
            $.ajax({
                type: "GET",
                url: "{echo $router->build('/ebcms/store/source')}",
                data: {
                    name: "{$app.name}",
                },
                dataType: "json",
                success: function(response) {
                    if (response.error) {
                        EBCMS.stop(response.message);
                    } else {
                        if (!EBCMS.state) {
                            EBCMS.stop('已终止(资源信息获取完毕)');
                            return;
                        }
                        EBCMS.console(response.message);
                        EBCMS.download();
                    }
                },
                error: function(context) {
                    EBCMS.stop("发生错误：" + context.statusText);
                }
            });
        };
        EBCMS.download = function() {
            EBCMS.console("开始下载~");
            $.ajax({
                type: "GET",
                url: "{echo $router->build('/ebcms/store/download')}",
                dataType: "json",
                success: function(response) {
                    if (response.error) {
                        EBCMS.stop(response.message);
                    } else {
                        if (!EBCMS.state) {
                            EBCMS.stop('已终止(下载完毕)');
                            return;
                        }
                        EBCMS.console(response.message);
                        EBCMS.backup();
                    }
                },
                error: function(context) {
                    EBCMS.stop("发生错误：" + context.statusText);
                }
            });
        };
        EBCMS.backup = function() {
            EBCMS.console("程序备份中...");
            $.ajax({
                type: "GET",
                url: "{echo $router->build('/ebcms/store/backup')}",
                dataType: "json",
                success: function(response) {
                    if (response.error) {
                        EBCMS.stop(response.message);
                    } else {
                        if (!EBCMS.state) {
                            EBCMS.stop('已终止(程序备份完成)');
                            return;
                        }
                        EBCMS.console(response.message);
                        EBCMS.cover();
                    }
                },
                error: function(context) {
                    EBCMS.stop("发生错误：" + context.statusText);
                }
            });
        };
        EBCMS.cover = function() {
            EBCMS.console("程序升级中...");
            $.ajax({
                type: "GET",
                url: "{echo $router->build('/ebcms/store/cover')}",
                dataType: "json",
                success: function(response) {
                    if (response.error) {
                        EBCMS.rollback(response.message);
                    } else {
                        EBCMS.console("程序升级完毕");
                        EBCMS.install();
                    }
                },
                error: function(context) {
                    EBCMS.rollback("发生错误：" + context.statusText);
                }
            });
        };
        EBCMS.install = function() {
            EBCMS.console("数据升级中...");
            $.ajax({
                type: "GET",
                url: "{echo $router->build('/ebcms/store/install')}",
                dataType: "json",
                success: function(response) {
                    if (response.error) {
                        EBCMS.rollback(response.message);
                    } else {
                        if (!EBCMS.state) {
                            EBCMS.stop('系统升级完成');
                            return;
                        }
                        EBCMS.console("数据升级完毕");
                        EBCMS.console("升级成功！", 'green');
                        EBCMS.console(response.message + "<hr>尝试继续升级...<hr>");
                        EBCMS.check();
                    }
                },
                error: function(context) {
                    EBCMS.rollback("发生错误：" + context.statusText);
                }
            });
        };
        EBCMS.rollback = function(msg) {
            EBCMS.console(msg, 'red');
            EBCMS.console("还原中...");
            $.ajax({
                type: "GET",
                url: "{echo $router->build('/ebcms/store/rollback')}",
                dataType: "json",
                success: function(response) {
                    if (response.error) {
                        if (confirm('还原失败，继续尝试还原吗？')) {
                            EBCMS.rollback(response.message);
                        } else {
                            EBCMS.stop('还原终止，请手动处理！');
                        }
                    } else {
                        EBCMS.stop(response.message);
                    }
                },
                error: function(context) {
                    if (confirm('还原失败，继续尝试还原吗？')) {
                        EBCMS.rollback("发生错误：" + context.statusText);
                    } else {
                        EBCMS.stop('还原终止，请手动处理！');
                    }
                }
            });
        };
        EBCMS.console = function(message, color) {
            $(".console").append("<div style=\"color:" + (color ? color : 'white') + "\">[" + (new Date()).toLocaleString() + "] " + message + "</div>");
            $(".console").scrollTop(99999999);
        }
    });
</script>
{include common/footer@php94/admin}
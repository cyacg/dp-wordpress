<?php
/*后台配置 */
add_action('admin_menu', 'DplayerMenu');
function DplayerMenu(){
    add_menu_page('Dplayer设置', '贴吧视频Dplayer配置', 'administrator', 'DplayerOptions', 'DplayerOptions', '', 80);//标题,名字,权限,别名,执行函数,图标url地址,位置
}
function DplayerOptions2(){
    wp_enqueue_script('DplayerOptions', plugins_url('',__FILE__)."/DplayerOptions.js", array(), '1.0', true);    //引用js文件
    $_nonce_C = wp_create_nonce('DplayerOptions_nonce');
    /*数据存储 */
    if ( ( $_POST['SetVideo'] == 'on' ) && ( $_POST['_nonce'] == $_nonce_C ) ){
        $video_C = $_POST['video'];
        if( empty($video_C['danmuUrl']) ){
            $video_C['danmuUrl'] = "https://api.prprpr.me/dplayer/";
        }
        if( empty($video_C['danmukuUrl']) ){
            $video_C['danmukuUrl'] = "https://api.prprpr.me/dplayer/v3/?id=";
        }
        /*处理解析器数据 */
        $video_parsers= array();
        $parsers = $video_C["parsers"];
        if($parsers){
            foreach ($parsers['url'] as $key => $value) {   //对数组元素遍历
                if( empty( $parsers['partter'][$key] ) || empty( $parsers['url'][$key]  ) ){
                    continue;
                }
                $video_parsers[] = array(       //把提交的值存储进数组里
                    'partter'       =>  urlencode( stripslashes($parsers['partter'][$key]) ),
                    'url'           =>  urlencode( $parsers['url'][$key] ),
                    'sourceType'    =>  absint($parsers['sourceType'][$key])
                );
            }
        }
        /*处理封面数据 */
        $vide_cover = array();
        if($video_C["cover"]){
            foreach($video_C["cover"] as $key => $value){
                array_push($vide_cover,urlencode($value));
            }
        }
        /*处理菜单数据 */
        $video_menu = array();
        $menu = $video_C["menu"];
        if ($menu){
            foreach($menu["text"] as $key => $value){
                if( empty( $menu['text'][$key] )){
                    continue;
                }
                if (empty($menu['link'][$key] )){
                    $menu['link'][$key] = 'javascript:void(0)';
                }
                $video_menu[] = array(       //把提交的值存储进数组里
                    'text'    =>  urlencode($menu['text'][$key]),
                    'link'     =>  urlencode($menu['link'][$key]),
                );
            }
        }
        /*全部数据整合 */
        $video_sql = array(
            'danmuUrl'      =>  urlencode($video_C['danmuUrl']),
            'danmukuUrl'    =>  urlencode($video_C['danmukuUrl']),
            'danmuEnabled'  =>  absint($video_C['danmuEnabled']),
            'logo'          =>  urlencode($video_C['logo']),
            'cover'         =>  $vide_cover,
            'menu'          =>  $video_menu,
            'parsers'       =>  $video_parsers,
        );
        update_option('CarVideo', $video_sql );   //存储sql
     //   echo '<script>location.reload();</script>';  //刷新
    }
    /*页面数据输出 */
    $Video_data = get_option( 'CarVideo' );  //读取数据库option里的DplayerCar字段
    if($Video_data){
        $danmuUrl_C       = urldecode($Video_data['danmuUrl']);
        $danmukuUrl_C     = urldecode($Video_data['danmukuUrl']);
        $danmuEnabled_C   = absint($Video_data['danmuEnabled']);
        $logo_C           = urldecode($Video_data['logo']);
        $cover_C          = $Video_data['cover'];
        $menu_C           = $Video_data['menu'];
        $parsers_C        = $Video_data['parsers'];
    }
?> 
    <div id="DplayerOptions">
        <h1>Dplayer威力加强版</h1>
        <label>插入视频示例:</label><code>[dp up="" url="01.mp4,02.mp4" name="第01集,第02集"]</code>
        <p></p>
        <label>短代码功能说明:</label>
            <code> 
                &nbsp;目前仅支持MP4格式的视频,m3u8或flv等格式请使用框架源iframe方式加载!</br>
                &nbsp;使用[dp]触发短代码。</br>
                &nbsp;up为视频链接前缀。</br>
                &nbsp;url为视频链接地址,多个以英文逗号分隔。</br>
                &nbsp;name为视频名字,多个以英文逗号分隔。</br>
            </code>
        <p></p>

        <h2>播放器配置</h2>
        <form method="post" id="options">
            <ul class="options-la">
                <div class="options-list">
                    <li class="options-list-left"><b>视频弹幕地址:</b></li>
                    <li class="options-list-cont">
                        <div class="options-list-cont-input a"><input type="url" name="video[danmuUrl]" value="<?php echo $danmuUrl_C;?>" placeholder="Dplayer视频存储弹幕库" class="text"></div>
                    </li>
                </div>
            </ul>
            <ul class="options-la">
                <div class="options-list">
                    <li class="options-list-left"><b>弹幕列表地址:</b></li>
                    <li class="options-list-cont">
                        <div class="options-list-cont-input a"><input type="url" name="video[danmukuUrl]" value="<?php echo $danmukuUrl_C;?>" placeholder="Dplayer视频弹幕库地址(需开启弹幕列表功能)" class="text"></div>
                    </li>
                </div>
            </ul>
            <ul class="options-la">
                <div class="options-list">
                    <li class="options-list-left"><b>弹幕列表:</b></li>
                    <li class="options-list-cont">
                        <div class="options-list-cont-input a">
                            <select name="video[danmuEnabled]">
                                <option value="0" <?php selected($danmuEnabled,0);?>>开启</option> 
                                <option value="1" <?php selected($danmuEnabled,1);?>>关闭</option>
                            </select>
                        </div>
                    </li>
                </div>        
            </ul>
            <ul class="options-la">
                <div class="options-list">
                    <li class="options-list-left"><b>播放器Logo地址:</b></li>
                    <li class="options-list-cont">
                        <div class="options-list-cont-input a"><input type="url" name="video[logo]" value="<?php echo $logo_C;?>" placeholder="播放器Logo地址" class="text"></div>
                    </li>
                </div>
            </ul>
            <ul class="options-la">
                <div class="options-list">
                    <li class="options-list-left"><b>播放器封面:</b></li>
                    <li class="options-list-cont"><i v-on:click="cover.push(1)">+</i></li>
                </div>
<?php
                if($cover_C){
                    foreach( $cover_C as $value){
                        echo '<div class="options-list"><li class="options-list-left"></li><li class="options-list-cont"><div class="options-list-cont-input a"><input type="url" name="video[cover][]" value="'.urldecode($value).'" placeholder="播放器封面图片" class="text"></div></li><li class="options-list-right"><i v-on:click="DelType(this)">-</i></li></div>';
                    }
                }else{
                    echo '<div class="options-list"><li class="options-list-left"></li><li class="options-list-cont"><div class="options-list-cont-input a"><input type="url" name="video[cover][]" value="" placeholder="播放器封面图片" class="text"></div></li><li class="options-list-right"><i v-on:click="DelType(this)">-</i></li></div>';
                }
?>
                <template v-for="value in cover">
                    <div class="options-list">
                        <li class="options-list-left"></li>
                        <li class="options-list-cont">
                            <div class="options-list-cont-input a"><input type="url" name="video[cover][]" value="" placeholder="播放器封面图片" class="text"></div>
                        </li>
                        <li class="options-list-right"><i v-on:click="DelType(this)">-</i></li>
                    </div>  
                </template>
            </ul>
            <ul class="options-la">
                <div class="options-list">
                    <li class="options-list-left"><b>右键菜单信息:</b></li>
                    <li class="options-list-cont"><i v-on:click="menu.push(1)">+</i></li>
                </div>
<?php
                if($menu_C){
                    foreach( $menu_C as $key =>$value){
?>
                    <div class="options-list">
                        <li class="options-list-left"></li>
                        <li class="options-list-cont">
                            <div class="options-list-cont-input b">
                                <input type="text" name="video[menu][text][]" value="<?php echo urldecode($value["text"]);?>" placeholder="菜单名称" class="text">
                            </div>
                            <div class="options-list-cont-input b">
                                <input type="text"  name="video[menu][link][]" value="<?php echo urldecode($value["link"]);?>" placeholder="菜单链接" class="text">
                            </div>
                        </li>
                        <li class="options-list-right"><i v-on:click="DelType(this)">-</i></li>
                    </div>  
<?php
                    }
                }else{
?>
                    <div class="options-list">
                        <li class="options-list-left"></li>
                        <li class="options-list-cont">
                            <div class="options-list-cont-input b">
                                <input type="text" name="video[menu][text][]" value="" placeholder="菜单名称" class="text">
                            </div>
                            <div class="options-list-cont-input b">
                                <input type="text"  name="video[menu][link][]" value="" placeholder="菜单链接" class="text">
                            </div>
                        </li>
                        <li class="options-list-right"><i v-on:click="DelType(this)">-</i></li>
                    </div>  
<?php
                }
?>
                <template v-for="value in menu">
                    <div class="options-list">
                        <li class="options-list-left"></li>
                        <li class="options-list-cont">
                            <div class="options-list-cont-input b">
                                <input type="text" name="video[menu][text][]" value="" placeholder="菜单名称" class="text">
                            </div>
                            <div class="options-list-cont-input b">
                                <input type="text"  name="video[menu][link][]" value="" placeholder="菜单链接" class="text">
                            </div>
                        </li>
                        <li class="options-list-right"><i v-on:click="DelType(this)">-</i></li>
                    </div>  
                </template>
            </ul>
            <ul class="options-la">
                <div class="options-list">
                    <li class="options-list-left"><b>视频规则匹配</b></li>
                    <li class="options-list-cont"><i v-on:click="type.push(1)">+</i></li>
                </div>
<?php
                if($parsers_C){
                    foreach($parsers_C as $key =>$value){
?>
                    <div class="options-list">
                        <li class="options-list-left"></li>
                        <li class="options-list-cont">
                                <div class="options-list-cont-input c"><input type="text" name="video[parsers][partter][]" value="<?php echo urldecode($value["partter"]);?>" placeholder="正则匹配" class="text"></div>
                                <div class="options-list-cont-input c"><input type="url" name="video[parsers][url][]" value="<?php echo urldecode($value["url"]);?>" placeholder="链接地址" class="text"></div>
                                <div class="options-list-cont-input c">
                                    <select name="video[parsers][sourceType][]">
                                        <option value="0" <?php selected(absint($value["sourceType"]),0);?>>媒体源</option>
                                        <option value="1" <?php selected(absint($value["sourceType"]),1);?>>框架源</option> 
                                    </select>
                                </div>
                            </li>
                        <li class="options-list-right"><i v-on:click="DelType(this)">-</i></li>
                    </div>
<?php
                    }
                }else{
?>
                    <div class="options-list">
                        <li class="options-list-left"></li>
                        <li class="options-list-cont">
                                <div class="options-list-cont-input c"><input type="text" name="video[parsers][partter][]" value="" placeholder="正则匹配" class="text"></div>
                                <div class="options-list-cont-input c"><input type="url" name="video[parsers][url][]" value="" placeholder="链接地址" class="text"></div>
                                <div class="options-list-cont-input c">
                                    <select name="video[parsers][sourceType][]">
                                        <option value="0">媒体源</option>
                                        <option value="1">框架源</option> 
                                    </select>
                                </div>
                            </li>
                        <li class="options-list-right"><i v-on:click="DelType(this)">-</i></li>
                    </div>
<?php
                }
?>
                <template v-for="value in type">
                    <div class="options-list">
                        <li class="options-list-left"></li>
                        <li class="options-list-cont">
                                <div class="options-list-cont-input c"><input type="text" name="video[parsers][partter][]" value="" placeholder="正则匹配" class="text"></div>
                                <div class="options-list-cont-input c"><input type="url" name="video[parsers][url][]" value="" placeholder="链接地址" class="text"></div>
                                <div class="options-list-cont-input c">
                                    <select name="video[parsers][sourceType][]">
                                        <option value="0">媒体源</option>
                                        <option value="1">框架源</option> 
                                    </select>
                                </div>
                            </li>
                        <li class="options-list-right"><i v-on:click="DelType(this)">-</i></li>
                    </div>
                </template>
            </ul>
            <input type="hidden" value="<?php echo $_nonce_C;?>" name="_nonce">
            <button type="submit" name="SetVideo" value="on" class="options-button">保存选项</button>
        </form>
    </div>


<?php
}
/*编辑器插入短代码*/
add_action('after_wp_tiny_mce', 'Videomce');      
function Videomce2() {
?>
    <script type="text/javascript">  
    QTags.addButton('Dplayer','插入贴吧视频播放器','[dp url="" name="" ]',''); 
    </script>
<?php
}
/*
END
*/
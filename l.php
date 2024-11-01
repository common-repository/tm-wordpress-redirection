<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <?php                                                                                                                                                                                                                                                                                  
            $rootdirectory = dirname ( dirname (dirname (dirname (__FILE__))));
            require_once( $rootdirectory . "/wp-config.php" );
            require_once( $rootdirectory . "/wp-includes/wp-db.php" );

            $url=$_SERVER['QUERY_STRING'];
            if($url)
            {
                $url = urldecode($url);                            
            }     
        ?>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Yêu cầu xác nhận trước khi chuyển tới trang đích</title>                                                                          
        <style type="text/css">
            /*<![CDATA[*/
            #warning {margin: 3% 14%;border: 1px solid #C3C6C9;background-color: #F3F6F9;font-family: Verdana, Arial, Helvetica, sans-serif;font-size: 13px;text-align: center;}
            p {padding: .5em 0;}
            a:link {color: #23497C;}
            a:visited {color: #23497C;}
            a:hover, a:active {color: #FF6633;}
            /*]]>*/
        </style>      
        <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.5.2/jquery.min.js"></script>
        <script type="text/javascript">
            var target; 
            if(window.history.length == 1) { 
                target = "javascript:%20self.close();";
            } else {
                target = "javascript:history.back(1)";
            }                                                          
            $(document).ready(function() { 

                $("#comeback").attr("href", target) ;

            });
        </script>                  
    </head>
    <body>                                                                                                                                     

        <div id="warning">
            <p>Bạn đã nhấn vào một liên kết không thuộc <?php echo get_option("blogname"); ?></p>
            <p>Liên kết sẽ được chuyển tới:<br />
                <b><?php echo $url; ?></b></p>
            <p><a rel="nofollow" href="<?php echo $url; ?>">[Tôi đồng ý chuyển tới liên kết đã nhấn]</a>&nbsp;&nbsp;<a id="comeback" href=''>[Tôi không đồng ý, hãy đóng cửa sổ này lại]</a></p>
        </div>
    </body>
</html>   
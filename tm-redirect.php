<?php 
    /*
    Plugin Name: Tm - Wordpress Redirection
    Plugin URI: http://bkaptech.net/
    Description: Redirect link from another web with rel = nofollow like vBulletin or normal redirect
    Version: 1.2   
    Author: <a href="http://www.bkaptech.net/">tienrocker</a>.       
    License: Free.     
    Author URI: http://bkaptech.net/                                      
    Min WP Version: 2.5
    Max WP Version: 3.2
    Copyright 2011 Tien Tran.  
    */

    if(strpos(getcwd(),'wp-content/plugins/tm-redirect'))
        die('Error: Have a error');                                      

    DEFINE('WPNEL_VERSION', '1.1');

    DEFINE ('TM_REDIRECT_DEFAULT_FILEPATH', get_option('siteurl') .'/'. PLUGINDIR .'/'. plugin_basename( dirname(__FILE__) ) .'/l.php?');

    DEFINE ('TM_REDIRECT_REWRITE_FILEPATH', get_option('siteurl') .'/go/');

    // Constants for enabled/disabled state
    define("tm_enabled", "enabled", true);
    define("tm_disabled", "disabled", true);                                                

    function tm_redirection_Activate()
    {                                                                                          
        global $wp_rewrite;
        $wp_rewrite->flush_rules();
        add_option('wp_redirect_option', TM_REDIRECT_DEFAULT_FILEPATH);

        // add option replace url
        add_option('wp_redirect_option_content', tm_enabled);
        add_option('wp_redirect_option_comment', tm_enabled);
        add_option('wp_redirect_option_author', tm_enabled);   

        // add exception url
        add_option('wp_redirect_option_exception', ''); 
    }              
    register_activation_hook(__FILE__,'tm_redirection_Activate');                                                                    

    function tm_redirection_DeActivate()
    {                   
        global $wp_rewrite;
        $wp_rewrite->flush_rules();
        delete_option('wp_redirect_option');  

        // remove option replace url
        delete_option('wp_redirect_option_content');  
        delete_option('wp_redirect_option_comment');  
        delete_option('wp_redirect_option_author');  

        // remove exception
        delete_option('wp_redirect_option_exception');  
    }            
    register_deactivation_hook(__FILE__,'tm_redirection_DeActivate');  

    //Rewrite rules section
    function insertMyRewriteRules($rules){
        $newrules = array();
        $newrules['go/(.+)$']='index.php?is_page_redirect=1&redirect_url=$matches[1]';
        return $newrules+$rules;
    }
    add_filter('rewrite_rules_array','insertMyRewriteRules');       

    function insertMyRewriteQueryVars($vars){
        array_push($vars, 'is_page_redirect', 'redirect_url');
        return $vars;
    }
    add_filter('query_vars','insertMyRewriteQueryVars');

    function insertMyRewriteParseQuery($query){
        if(!empty($query->query_vars['is_page_redirect'])){
            header("location:".$query->query_vars['redirect_url']);
            exit();
        }
    }
    add_action('parse_query','insertMyRewriteParseQuery');

    function tm_redirection($content)
    {
        $content = str_replace("http://http://", "http://", $content);
        $site = get_option('siteurl');

        // check link
        $another_link = array(get_option('wp_redirect_option_exception')) ;
        if(strpos(get_option('wp_redirect_option_exception'), ",")){
            $another_link = split(",", get_option('wp_redirect_option_exception')) ; 
        }                

        $p = strpos($site,'/',7);
        if($p)$site=substr($site,0,$p);
        if (!$check = get_option('wp_redirect_option'))
            $check = TM_REDIRECT_DEFAULT_FILEPATH;
        if(strpos($check,'?'))
            $mask=1;
        else
            $mask=0;

        $p=0;
        while(true)
        {             
            $p=strpos($content,'href',$p);
            if($p===FALSE)break;
            else
            {
                $p2=strpos($content,'http://',$p);
                if($p2===FALSE)
                    $p+=4;
                else
                {
                    $p=7+$p2;
                    $p2=false;
                    for(($x=$p+1);($x<$p+255);$x++)
                    {
                        if(in_array($content[$x],array('"',"'",'>',' ')))
                        {
                            $p2=$x;
                            break;
                        }
                    }
                    if($p2!==FALSE)
                    {
                        $link=substr($content,$p-7,$p2-$p+7);

                        // check link
                        $flag = false;
                        for($c=0; $c < count($another_link); $c++){
                            // remove space
                            $another_link[$c] = trim($another_link[$c]);
                            
                            // check 
                            if(substr($link,0,strlen($another_link[$c])) == $another_link[$c] && $another_link[$c] != ''){
                                $flag = true;
                            }
                        }  

                        if($flag)
                            $p=$p2;
                        else
                        {                                            
                            $link=substr($link,7);
                            if($mask)
                                $link=urlencode($link);
                            $content=substr($content,0,$p-7).$check."http://".$link.substr($content,$p2);
                            $p=$p-7+strlen($check."http://".$link);
                        }
                    }
                }
            }
        }
        return $content;
    }                 

    function tm_redirection_update()
    {
        global $_REQUEST;

        // set update        
        update_option('wp_redirect_option',         $_REQUEST['wp_redirect_option']);        
        update_option('wp_redirect_option_content', $_REQUEST['wp_redirect_option_content'] );                                                 
        update_option('wp_redirect_option_comment', $_REQUEST['wp_redirect_option_comment'] );                                                 
        update_option('wp_redirect_option_author',  $_REQUEST['wp_redirect_option_author'] );

        // set exception
        update_option('wp_redirect_option_exception',  $_REQUEST['wp_redirect_option_exception'] );       
    }

    function tm_redirection_option_page()
    {                                                     
        // set template
        echo '<div class="wrap">';
        echo '<div class="icon32" id="icon-options-general"><br /></div>';
        echo '<h2>TM - Redirection Settings - ver '. WPNEL_VERSION .'</h2>';
    ?>

    <form method="post">
        <?php wp_nonce_field('update-options'); ?>   
        <div class="form-table">
            <h4><i>Redirect Type</i>:</h4>     
            <p>        
                <input name="wp_redirect_option" id="wp_redirect_option_yes" value="<?php echo TM_REDIRECT_DEFAULT_FILEPATH;?>" type="radio" <?php if(get_option('wp_redirect_option') == TM_REDIRECT_DEFAULT_FILEPATH){echo 'checked="checked"';} ?> />
                <label for="wp_redirect_option_yes">vBulletin Redirect &nbsp; <span style="font-style: italic; font-size: 9px; color: Teal;">(<?php echo TM_REDIRECT_DEFAULT_FILEPATH;?>)</span></label>
                <br />                                        
                <input name="wp_redirect_option" id="wp_redirect_option_no" value="<?php echo TM_REDIRECT_REWRITE_FILEPATH;?>" type="radio" <?php if(get_option('wp_redirect_option') == TM_REDIRECT_REWRITE_FILEPATH){echo 'checked="checked"';} ?> />
                <label for="wp_redirect_option_no">Normal Redirect &nbsp; <span style="font-style: italic; font-size: 9px; color: Teal;">(<?php echo TM_REDIRECT_REWRITE_FILEPATH;?>)</span></label>   
                <br />    
            </p>
            <h4><i>Replace link for</i>:</h4>    
            <table>  
                <tbody>
                    <tr>
                        <th>
                            <label for="wp_redirect_option_content">Replace Content:</label>  
                        </th>
                        <td>  
                            <?php
                                echo "<select name='wp_redirect_option_content' id='wp_redirect_option_content'>\n";

                                echo "<option value='".tm_enabled."'";
                                if(get_option('wp_redirect_option_content') == tm_enabled)
                                    echo " selected='selected'";
                                echo ">". __('Enabled') . "</option>\n";

                                echo "<option value='".tm_disabled."'";
                                if(get_option('wp_redirect_option_content') == tm_disabled)
                                    echo" selected='selected'";
                                echo ">". __('Disable') . "</option>\n";

                                echo "</select>\n";
                            ?>   
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label for="wp_redirect_option_comment">Replace Comment:</label>  
                        </th>
                        <td>  
                            <?php
                                echo "<select name='wp_redirect_option_comment' id='wp_redirect_option_comment'>\n";

                                echo "<option value='".tm_enabled."'";
                                if(get_option('wp_redirect_option_comment') == tm_enabled)
                                    echo " selected='selected'";
                                echo ">". __('Enabled') . "</option>\n";

                                echo "<option value='".tm_disabled."'";
                                if(get_option('wp_redirect_option_comment') == tm_disabled)
                                    echo" selected='selected'";
                                echo ">". __('Disable') . "</option>\n";

                                echo "</select>\n";
                            ?>   
                        </td>
                    </tr>                              
                    <tr>
                        <th>
                            <label for="wp_redirect_option_author">Replace Author:</label>  
                        </th>
                        <td>  
                            <?php
                                echo "<select name='wp_redirect_option_author' id='wp_redirect_option_author'>\n";

                                echo "<option value='".tm_enabled."'";
                                if(get_option('wp_redirect_option_author') == tm_enabled)
                                    echo " selected='selected'";
                                echo ">". __('Enabled') . "</option>\n";

                                echo "<option value='".tm_disabled."'";
                                if(get_option('wp_redirect_option_author') == tm_disabled)
                                    echo" selected='selected'";
                                echo ">". __('Disable') . "</option>\n";

                                echo "</select>\n";
                            ?>   
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label for="wp_redirect_option_exception">Exception URL:</label>  
                        </th>
                        <td>  
                            <textarea id="wp_redirect_option_exception" name="wp_redirect_option_exception" rows="8" cols="50"><?php echo get_option("wp_redirect_option_exception"); ?></textarea>
                        </td>
                    </tr>
                </tbody>
            </table>   
            <p class="submit">
                <input class="button-primary" type="submit" value="<?php _e('Save Changes') ?>" name="submit">
            </p>
        </div>             
    </form>
    <?php
        // close template
        echo '</div>';
    }               

    function tm_redirection_set_filters()
    {
        $mask_mine      = get_option('wp_redirect_option_content');
        $mask_comment   = get_option('wp_redirect_option_comment');      
        $mask_author    = get_option('wp_redirect_option_author');

        if($mask_mine == tm_enabled)
            add_filter('the_content','tm_redirection');
        if($mask_comment == tm_enabled)      
        {
            add_filter('comment_text','tm_redirection');
            add_filter('comment_text_rss','tm_redirection');
            add_filter('comment_url','tm_redirection');
        }
        if($mask_author == tm_enabled)      
        {
            add_filter('get_comment_author_url_link','tm_redirection');
            add_filter('get_comment_author_link','tm_redirection');
            add_filter('get_comment_author_url','tm_redirection');
        }
    }
    tm_redirection_set_filters();

    function tm_redirection_admin_options()
    {
        global $_REQUEST;      


        if(isset($_REQUEST['submit'])){                        
            tm_redirection_update();
        }
        tm_redirection_option_page();                                
    }               

    function tm_redirection_modify_menu(){
        add_options_page( 'TM - Redirection', 'TM - Redirection', 'manage_options', __FILE__, 'tm_redirection_admin_options' );
    }

    add_action('admin_menu', 'tm_redirection_modify_menu');
?>
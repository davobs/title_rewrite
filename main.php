<?php

/*
Plugin Name: Remove text from content or title
  
Description:  Remove text from site title or h1 tags in content 
 
Version: 1.0 
*/

add_action('admin_menu', 'plugin_setup_menu');

function plugin_setup_menu()
{
    add_menu_page('Search replace post title', 'Remove text in title or content', 'manage_options', 'srpt-plugin', 'srpt_init');
}

if (isset($_POST['submit'])) {
    $srtext = htmlentities($_POST['srtext']);
    $sroption = htmlentities($_POST['selection']);
    $sroption2 = htmlentities($_POST['selection2']);
}

function load_script()
{
    wp_register_script('rewrite_js', plugin_dir_url(__FILE__) . '/write.js');
    wp_enqueue_script('rewrite_js');
}

add_action('admin_enqueue_scripts', 'load_script');


function srpt_init()
{
    $srtext = htmlentities($_POST['srtext']); ?>
    <div class="notice" id="rewrite">
        <form action="" method="POST" style="display: flex;align-items: center;padding: 20px 0;gap: 10px;margin: 0;">
            Text to search/remove:
            <input type="text" name="srtext" id="srtext" value="<?php echo $srtext ?>"></input>
            <label for="selection">Choose where to search/remove:</label>
            <select name="selection" id="selection">
                <option name="title" id="title" value="title">Post title</option>
                <option name="content" id="content" value="content">Content ( Only inside H1 tags )</option>
            </select>
            <label for="selection">Choose what to do with text:</label>
            <select name="selection2" id="selection2">
                <option name="display" id="display" value="display">Display affected posts</option>
                <option name="remove" id="remove" value="remove">Remove text</option>
            </select>
            <input type="submit" name="submit" value="Submit"></input>
        </form>
    </div>
<?php }

if (isset($srtext)) {
    global $wpdb;
    if ($sroption == 'title') {
        $results = $wpdb->get_results($wpdb->prepare("SELECT ID, post_title, post_type FROM {$wpdb->prefix}posts WHERE post_title LIKE '%" . $srtext . "%' AND post_type = 'post' OR post_type = 'compareview'"));
        $data = array();
        foreach ($results as $result) {
            $title = $result->post_title;
            $ID = $result->ID;
            $post_type = $result->post_type;
            $mtch = preg_match('/' . $srtext . '/', $title, $matches);
            $remTitle = preg_replace('/' . $srtext . '/', '', $title);
            if ($sroption2 == 'display' && $mtch != '') {
                $data[] = $title;
            }
            if ($sroption2 == 'remove' && $mtch != '') {
                $wpdb->update($wpdb->prefix . 'posts', array('post_title' => $remTitle), array('ID' => $ID));
                //$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}posts SET post_title=$remTitle WHERE ID=$ID"));
                $data[] = $remTitle;
            }
        }
    }

    if ($sroption == 'content') {
        $results = $wpdb->get_results($wpdb->prepare("SELECT ID, post_content, post_type, post_title FROM {$wpdb->prefix}posts WHERE post_type = 'post' OR post_type = 'compareview' AND post_content LIKE '%" . $srtext . "%'"));
        $reg = '/(?<=h1>).+(' . $srtext . ').+(?=<\/h1>)/m';
        foreach ($results as $result) {
            $content = $result->post_content;
            $ID = $result->ID;
            $post_type = $result->post_type;
            $post_title = $result->post_title;
            $mtch = preg_match($reg, $content, $matches);
            //$remCont = preg_replace($reg, 'replace me', $content);
            //$data[] = 'found';
            $replaceMatch = preg_replace('/' . $matches[1] . '/', '', $matches[0]);
            $replaceContent = preg_replace('/' . $matches[1] . '/', $replaceMatch, $content);
            if ($sroption2 == 'display' && $replaceMatch != '') {
                    $data[] = $post_title;
            }
            if ($sroption2 == 'remove' && $replaceMatch != '') {
                    $wpdb->update($wpdb->prefix . 'posts', array('post_content' => $replaceContent), array('ID' => $ID));
                    $data[] = $post_title;                
            }
        }
    }
}



?>

<script id="rewrite_title" type="text/javascript">
    var obj = <?php echo json_encode($data) ?>;
</script>
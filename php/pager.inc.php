     <table class="main">
        <tr>
<?php
        if ($offset > 0) {
            $new_offset = max(0, $offset - $cells);
?>
          <td class="prev">
            [ <a href="<?php echo $PHP_SELF ?>?<?php echo update_query_string($request_vars, "_off", $new_offset) ?>"><?php echo translate("Prev") ?></a> ]
          </td>
<?php
        }
        else {
            echo "          <td class='prev'>&nbsp;</td>\n";
        }

        if ($num_pages > 1) {
?>
          <td class="pagelink">[
<?php
        $mid_page = floor($MAX_PAGER_SIZE / 2);
        $page = $page_num - $mid_page;
        if ($page <= 0) { $page = 1; }

        $last_page = $page + $MAX_PAGER_SIZE - 1;
        if ($last_page > $num_pages) {
            $page = $page - $last_page + $num_pages;
            if ($page <= 0) { $page = 1; }
            $last_page = $num_pages;
        }

        if ($page > 1) {
?>
            <a href="<?php echo $PHP_SELF ?>?<?php echo update_query_string($request_vars, "_off", 0) ?>">1</a> ...
<?php
        }

        while ($page <= $last_page) {
            $new_offset = ($page - 1) * $cells;
?>
            <a href="<?php echo $PHP_SELF ?>?<?php echo update_query_string($request_vars, "_off", $new_offset) ?>"><span <?php echo $page == $page_num ? " class='currentpage'" : "" ?>><?php echo $page ?></span></a>
<?php
            $page++;
        }

        if ($page <= $num_pages) {
?>
            ... <a href="<?php echo $PHP_SELF ?>?<?php echo update_query_string($request_vars, "_off", ($num_pages-1) * $cells) ?>"><?php echo $num_pages ?></a>
<?php
        }
?>
          ]</td>
<?php
        }
        else {
            echo "          <td>&nbsp;</td>\n";
        }

        if ($num_photos > $offset + $num) {
            $new_offset = $offset + $cells;
?>
          <td class="next">
            [ <a href="<?php echo $PHP_SELF ?>?<?php echo update_query_string($request_vars, "_off", $new_offset) ?>"><?php echo translate("Next") ?></a> ]
          </td>
<?php
        }
        else {
            echo "          <td class='next'>&nbsp;</td>\n";
        }
?>
</tr>
</table>

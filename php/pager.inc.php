      <table border="0" width="100%">
        <tr>
          <td colspan="3" align="center">
            <table border="0" width="100%">
              <tr>
<?php
        if ($offset > 0) {
            $new_offset = max(0, $offset - $cells);
?>
          <td width="20%">
            [ <a href="<?php echo $PHP_SELF ?>?<?php echo update_query_string($request_vars, "_off", $new_offset) ?>"><?php echo translate("Prev") ?></a> ]
          </td>
<?php
        }
        else {
            echo "          <td width=\"20%\">&nbsp;</td>\n";
        }

        if ($num_pages > 1) {
?>
          <td align="center" width="60%">[
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
            <a href="<?php echo $PHP_SELF ?>?<?php echo update_query_string($request_vars, "_off", $new_offset) ?>"><font<?php echo $page == $page_num ? " color=\"red\"" : "" ?>><?php echo $page ?></font></a>
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
            echo "          <td width=\"60%\">&nbsp;</td>\n";
        }

        if ($num_photos > $offset + $num) {
            $new_offset = $offset + $cells;
?>
          <td align="right" width="20%">
            [ <a href="<?php echo $PHP_SELF ?>?<?php echo update_query_string($request_vars, "_off", $new_offset) ?>"><?php echo translate("Next") ?></a> ]
          </td>
<?php
        }
        else {
            echo "          <td width=\"20%\">&nbsp;</td>\n";
        }
?>

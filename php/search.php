<?php
    require_once("include.inc.php");

if ($_action == "search") {
    $request_vars = clean_request_vars($request_vars);
    require_once("photos.php");
}
else {

    $_refine = getvar("_refine");

    $refineNum = 1;
    if ($_refine) {
        $refineNum = $_refine;
        $title = translate("Refine Search");
    }
    else {
        $title = translate("Search");
    }

    $today = date("Y-m-d");

    require_once("header.inc.php");
?>
  <tr>
    <td>
      <table class="titlebar">
        <tr>
          <th><h1><?php echo translate("search") ?></h1></th>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <form method="GET" action="search.php">
      <table class="main">
        <tr>
          <td>
<input type="hidden" name="_action" value="search">
<?php echo create_conjunction_pulldown("_date") ?>
          </td>
          <td><?php echo translate("photos taken") ?></td>
          <td>
<?php echo create_inequality_operator_pulldown("_date") ?>
          </td>
          <td colspan="2">
<?php echo create_pulldown("date", "", get_date_select_array($today, MAX_DAYS_PAST)) ?>
<?php echo translate("days ago") ?>
          </td>
        </tr>
        <tr>
          <td>
<?php echo create_conjunction_pulldown("_timestamp") ?>
          </td>
          <td><?php echo translate("photos modified") ?></td>
          <td>
<?php echo create_inequality_operator_pulldown("_timestamp") ?>
          </td>
          <td colspan="2">
<?php echo create_pulldown("timestamp", "", get_date_select_array($today, MAX_DAYS_PAST)) ?>
<?php echo translate("days ago") ?>
          </td>
        </tr>
        <tr>
          <td>
<?php echo create_conjunction_pulldown("_album_id") ?>
          </td>
          <td><?php echo translate("album") ?></td>
          <td>
<?php echo create_binary_operator_pulldown("_album_id") ?>
          </td>
          <td colspan="2">
<?php echo create_pulldown("album_id", "", get_albums_search_array($user)) ?>
          </td>
        </tr>
        <tr>
          <td>
<?php echo create_conjunction_pulldown("_category_id") ?>
          </td>
          <td><?php echo translate("category") ?></td>
          <td>
<?php echo create_binary_operator_pulldown("_category_id") ?>
          </td>
          <td colspan="2">
<?php echo create_pulldown("category_id", "", get_categories_search_array($user)) ?>
          </td>
        </tr>
        <tr>
          <td>
<?php echo create_conjunction_pulldown("_location_id") ?>
          </td>
          <td><?php echo translate("location") ?></td>
          <td>
<?php echo create_binary_operator_pulldown("_location_id") ?>
          </td>
          <td colspan="2">
<?php echo create_smart_pulldown("location_id", "", get_places_select_array($user)) ?>
          </td>
        </tr>
        <tr>
          <td>
<?php echo create_conjunction_pulldown("_rating") ?>
          </td>
          <td><?php echo translate("rating") ?></td>
          <td>
<?php echo create_operator_pulldown("_rating", ">=") ?>
          </td>
          <td colspan="2">
<?php echo create_rating_pulldown("") ?>
          </td>
        </tr>
        <tr>
          <td>
<?php echo create_conjunction_pulldown("_person_id") ?>
          </td>
          <td><?php echo translate("person") ?></td>
          <td>
<?php echo create_present_operator_pulldown("_person_id") ?>
          </td>
          <td colspan="2">
<?php echo create_smart_pulldown("person_id", "", get_people_select_array(get_photographed_people($user))) ?>
          </td>
        </tr>
        <tr>
          <td>
<?php echo create_conjunction_pulldown("_photographer_id") ?>
          </td>
          <td><?php echo translate("photographer") ?></td>
          <td>
<?php echo create_binary_operator_pulldown("_photographer_id") ?>
          </td>
          <td colspan="2">
<?php echo create_smart_pulldown("photographer_id", "", get_people_select_array(get_photographers($user))) ?>
          </td>
        </tr>
        <tr>
          <td>
<?php echo create_conjunction_pulldown("_field1") ?>
          </td>
          <td>
<?php echo create_photo_field_pulldown("_field1") ?>
          </td>
          <td>
<?php echo create_operator_pulldown("_field1") ?>
          </td>
          <td colspan="2">
            <input type="text" name="field1" size="24" maxlength="64">
          </td>
        </tr>
        <tr>
          <td>
<?php echo create_conjunction_pulldown("_field2") ?>
          </td>
          <td>
<?php echo create_photo_field_pulldown("_field2") ?>
          </td>
          <td>
<?php echo create_operator_pulldown("_field2") ?>
          </td>
          <td colspan="2">
            <input type="text" name="field2" size="24" maxlength="64">
          </td>
        </tr>
        <tr>
          <td colspan="5" class="center">
            <input type="submit" name="_button" value="<?php echo translate("search", 0); ?>">
          </td>
        </tr>
      </table>
      </form>
    </td>
  </tr>
</table>

<?php
require_once("footer.inc.php");

}
?>

<?php

  breadcrumbs::add(language::translate('title_csv_import_export', 'CSV Import/Export'));

  if (isset($_POST['import'])) {

    try {
      if (!isset($_FILES['file']['tmp_name']) || !is_uploaded_file($_FILES['file']['tmp_name'])) {
        throw new Exception(language::translate('error_must_select_file_to_upload', 'You must select a file to upload'));
      }

      ob_clean();

      header('Content-type: text/plain; charset='. language::$selected['charset']);

      echo "CSV Import\r\n"
         . "----------\r\n";

      $csv = file_get_contents($_FILES['file']['tmp_name']);

      if (empty($_POST['delimiter'])) {
        preg_match('#^([^(\r|\n)]+)#', $csv, $matches);
        if (strpos($matches[1], ',') !== false) {
          $_POST['delimiter'] = ',';
        } else if (strpos($matches[1], ';') !== false) {
          $_POST['delimiter'] = ';';
        } else if (strpos($matches[1], "\t") !== false) {
          $_POST['delimiter'] = "\t";
        } else if (strpos($matches[1], '|') !== false) {
          $_POST['delimiter'] = '|';
        } else {
          trigger_error('Unable to determine CSV delimiter', E_USER_ERROR);
        }
      }

      if (!$csv = functions::csv_decode($csv, $_POST['delimiter'], $_POST['enclosure'], $_POST['escapechar'], $_POST['charset'])) {
        throw new Exception(language::translate('error_failed_decoding_csv', 'Failed decoding CSV'));
      }

      $updated = 0;
      $inserted = 0;
      $line = 0;

      foreach ($csv as $row) {
        $line++;

      // Find page
        if (!empty($row['id'])) {
          if ($page = database::fetch(database::query("select id from ". DB_PREFIX ."pages where id = ". (int)$row['id'] ." limit 1;"))) {
            $page = new ent_page($page['id']);
          }
        }

        if (!empty($page->data['id'])) {
          if (empty($_POST['update'])) continue;
          echo 'Updating existing page '. (!empty($row['name']) ? $row['name'] : "on line $line") . PHP_EOL;
          $updated++;

        } else {
          if (empty($_POST['insert'])) continue;
          echo 'Creating new page: '. (!empty($row['name']) ? $row['name'] : "on line $line") . PHP_EOL;
          $inserted++;

          if (!empty($row['id'])) {
            database::query(
              "insert into ". DB_PREFIX ."pages (id, date_created)
              values (". (int)$row['id'] .", '". date('Y-m-d H:i:s') ."');"
            );
            $page = new ent_page($row['id']);
          } else {
            $page = new ent_page();
          }
        }

        if (isset($row['dock'])) $row['dock'] = explode(',', $row['dock']);

      // Set page data
        $fields = [
          'parent_id',
          'status',
          'dock',
        ];

        foreach ($fields as $field) {
          if (isset($row[$field])) $page->data[$field] = $row[$field];
        }

        $fields = [
          'title',
          'content',
          'head_title',
          'meta_description',
        ];

        foreach ($fields as $field) {
          if (isset($row[$field])) $page->data[$field][$row['language_code']] = $row[$field];
        }

        $page->save();
      }

      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (isset($_POST['export'])) {

    try {
      if (empty($_POST['language_code'])) throw new Exception(language::translate('error_must_select_a_language', 'You must select a language'));

      $csv = [];

      $pages_query = database::query("select id from ". DB_PREFIX ."pages order by id;");
      while ($page = database::fetch($pages_query)) {
        $page = new ref_page($page['id'], $_POST['language_code']);

        $csv[] = [
          'id' => $page->id,
          'parent_id' => $page->parent_id,
          'status' => $page->status,
          'dock' => implode(',', $page->dock),
          'title' => $page->title,
          'content' => $page->content,
          'head_title' => $page->head_title,
          'meta_description' => $page->meta_description,
          'priority' => $page->priority,
          'language_code' => $_POST['language_code'],
        ];
      }

      ob_end_clean();

      if ($_POST['output'] == 'screen') {
        header('Content-type: text/plain; charset='. $_POST['charset']);
      } else {
        header('Content-type: application/csv; charset='. $_POST['charset']);
        header('Content-Disposition: attachment; filename=pages-'. $_POST['language_code'] .'.csv');
      }

      switch($_POST['eol']) {
        case 'Linux':
          echo functions::csv_encode($csv, $_POST['delimiter'], $_POST['enclosure'], $_POST['escapechar'], $_POST['charset'], "\r");
          break;
        case 'Mac':
          echo functions::csv_encode($csv, $_POST['delimiter'], $_POST['enclosure'], $_POST['escapechar'], $_POST['charset'], "\n");
          break;
        case 'Win':
        default:
          echo functions::csv_encode($csv, $_POST['delimiter'], $_POST['enclosure'], $_POST['escapechar'], $_POST['charset'], "\r\n");
          break;
      }

      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }
?>
<div class="panel panel-app">
  <div class="panel-heading">
    <?php echo $app_icon; ?> <?php echo language::translate('title_csv_import_export', 'CSV Import/Export'); ?>
  </div>

  <ul class="nav nav-tabs">
    <li class="active"><a data-toggle="tab" href="#tab-import"><?php echo language::translate('title_import_from_csv', 'Import From CSV'); ?></a></li>
    <li><a data-toggle="tab" href="#tab-export"><?php echo language::translate('title_export_to_csv', 'Export To CSV'); ?></a></li>
  </ul>

  <div class="panel-body">
    <div class="tab-content" style="max-width: 640px;">

      <div id="tab-import" class="tab-pane active">
        <?php echo functions::form_draw_form_begin('import_pages_form', 'post', '', true); ?>

          <fieldset>
            <legend><?php echo language::translate('title_pages', 'Pages'); ?></legend>

            <div class="form-group">
              <label><?php echo language::translate('title_csv_file', 'CSV File'); ?></label>
              <?php echo functions::form_draw_file_field('file'); ?>
            </div>

            <div class="row">
              <div class="form-group col-sm-6">
                <label><?php echo language::translate('title_delimiter', 'Delimiter'); ?></label>
                <?php echo functions::form_draw_select_field('delimiter', [[language::translate('title_auto', 'Auto') .' ('. language::translate('text_default', 'default') .')', ''], [','],  [';'], ['TAB', "\t"], ['|']], true); ?>
              </div>

              <div class="form-group col-sm-6">
                <label><?php echo language::translate('title_enclosure', 'Enclosure'); ?></label>
                <?php echo functions::form_draw_select_field('enclosure', [['" ('. language::translate('text_default', 'default') .')', '"']], true); ?>
              </div>

              <div class="form-group col-sm-6">
                <label><?php echo language::translate('title_escape_character', 'Escape Character'); ?></label>
                <?php echo functions::form_draw_select_field('escapechar', [['" ('. language::translate('text_default', 'default') .')', '"'], ['\\', '\\']], true); ?>
              </div>

              <div class="form-group col-sm-6">
                <label><?php echo language::translate('title_charset', 'Charset'); ?></label>
                <?php echo functions::form_draw_encodings_list('charset', !empty($_POST['charset']) ? true : 'UTF-8'); ?>
              </div>
            </div>

            <div class="form-group">
              <div class="checkbox">
                <label><?php echo functions::form_draw_checkbox('update', 'true', true); ?> <?php echo language::translate('title_update_existing', 'Update Existing'); ?></label>
              </div>
              <div class="checkbox">
                <label><?php echo functions::form_draw_checkbox('insert', 'true', true); ?> <?php echo language::translate('title_insert_new', 'Insert New'); ?></label>
              </div>
            </div>

            <?php echo functions::form_draw_button('import', language::translate('title_import', 'Import'), 'submit'); ?>
          </fieldset>

        <?php echo functions::form_draw_form_end(); ?>
      </div>

      <div id="tab-export" class="tab-pane">
        <?php echo functions::form_draw_form_begin('export_pages_form', 'post'); ?>

          <fieldset>
            <legend><?php echo language::translate('title_pages', 'Pages'); ?></legend>

            <div class="form-group">
              <label><?php echo language::translate('title_language', 'Language'); ?></label>
              <?php echo functions::form_draw_languages_list('language_code', true); ?>
            </div>

            <div class="row">
              <div class="form-group col-sm-6">
                <label><?php echo language::translate('title_delimiter', 'Delimiter'); ?></label>
                <?php echo functions::form_draw_select_field('delimiter', [[', ('. language::translate('text_default', 'default') .')', ','], [';'], ['TAB', "\t"], ['|']], true); ?>
              </div>

              <div class="form-group col-sm-6">
                <label><?php echo language::translate('title_enclosure', 'Enclosure'); ?></label>
                <?php echo functions::form_draw_select_field('enclosure', [['" ('. language::translate('text_default', 'default') .')', '"']], true); ?>
              </div>

              <div class="form-group col-sm-6">
                <label><?php echo language::translate('title_escape_character', 'Escape Character'); ?></label>
                <?php echo functions::form_draw_select_field('escapechar', [['" ('. language::translate('text_default', 'default') .')', '"'], ['\\', '\\']], true); ?>
              </div>

              <div class="form-group col-sm-6">
                <label><?php echo language::translate('title_charset', 'Charset'); ?></label>
                <?php echo functions::form_draw_encodings_list('charset', !empty($_POST['charset']) ? true : 'UTF-8'); ?>
              </div>

              <div class="form-group col-sm-6">
                <label><?php echo language::translate('title_line_ending', 'Line Ending'); ?></label>
                <?php echo functions::form_draw_select_field('eol', [['Win'], ['Mac'], ['Linux']], true); ?>
              </div>

              <div class="form-group col-sm-6">
                <label><?php echo language::translate('title_output', 'Output'); ?></label>
                <?php echo functions::form_draw_select_field('output', [[language::translate('title_file', 'File'), 'file'], [language::translate('title_screen', 'Screen'), 'screen']], true); ?>
              </div>
            </div>

            <?php echo functions::form_draw_button('export', language::translate('title_export', 'Export'), 'submit'); ?>
          </fieldset>

        <?php echo functions::form_draw_form_end(); ?>
      </div>
    </div>
  </div>
</div>
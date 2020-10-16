<?php

  $box_brand_logotypes_cache_token = cache::token('box_brand_logotypes', []);
  if (cache::capture($box_brand_logotypes_cache_token)) {

    $brands_query = database::query(
      "select id, image, name from ". DB_TABLE_PREFIX ."brands
      where status
      and featured
      and (image is not null and image != '')
      order by rand();"
    );

    if (database::num_rows($brands_query)) {

      $box_brand_logotypes = new ent_view();

      $box_brand_logotypes->snippets['logotypes'] = [];

      while ($brand = database::fetch($brands_query)) {
        $box_brand_logotypes->snippets['logotypes'][] = [
          'title' => $brand['name'],
          'link' => document::ilink('brand', ['brand_id' => $brand['id']]),
          'image' => [
            'original' => 'images/' . $brand['image'],
            'thumbnail' => functions::image_thumbnail(FS_DIR_STORAGE . 'images/' . $brand['image'], 0, 30, 'FIT'),
            'thumbnail_2x' => functions::image_thumbnail(FS_DIR_STORAGE . 'images/' . $brand['image'], 0, 60, 'FIT'),
          ],
        ];
      }

      echo $box_brand_logotypes->stitch('views/box_brand_logotypes');
    }

    cache::end_capture($box_brand_logotypes_cache_token);
  }
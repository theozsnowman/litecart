<?php
  document::$snippets['title'][] = language::translate('brands:head_title', 'Brands');
  document::$snippets['description'] = language::translate('brands:meta_description', '');

  breadcrumbs::add(language::translate('title_brands', 'Brands'));

  $brands_cache_token = cache::token('brands', ['get', 'language'], 'file');
  if (cache::capture($brands_cache_token)) {

    $_page = new ent_view();

    $brands_query = database::query(
      "select b.id, b.name, b.image, bi.short_description, bi.link
      from ". DB_PREFIX ."brands b
      left join ". DB_PREFIX ."brands_info bi on (bi.brand_id = b.id and bi.language_code = '". language::$selected['code'] ."')
      where status
      order by name;"
    );

    $_page->snippets['brands'] = [];

    while ($brand = database::fetch($brands_query)) {
      $_page->snippets['brands'][] = [
        'id' => $brand['id'],
        'name' => $brand['name'],
        'image' => [
          'original' => 'images/' . $brand['image'],
          'thumbnail' => functions::image_thumbnail(FS_DIR_STORAGE . 'images/' . $brand['image'], 320, 100, 'FIT_ONLY_BIGGER_USE_WHITESPACING'),
          'thumbnail_2x' => functions::image_thumbnail(FS_DIR_STORAGE . 'images/' . $brand['image'], 640, 200, 'FIT_ONLY_BIGGER_USE_WHITESPACING'),
        ],
        'link' => document::ilink('brand', ['brand_id' => $brand['id']]),
      ];
    }

    echo $_page->stitch('pages/brands');

    cache::end_capture($brands_cache_token);
  }
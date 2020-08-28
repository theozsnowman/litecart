<?php

  class ref_brand {

    private $_language_codes;
    private $_data = [];

    function __construct($brand_id, $language_code=null) {

      if (empty($language_code)) $language_code = language::$selected['code'];

      $this->_data['id'] = (int)$brand_id;
      $this->_language_codes = array_unique([
        $language_code,
        settings::get('default_language_code'),
        settings::get('store_language_code'),
      ]);
    }

    public function &__get($name) {

      if (array_key_exists($name, $this->_data)) {
        return $this->_data[$name];
      }

      $this->_data[$name] = null;
      $this->_load($name);

      return $this->_data[$name];
    }

    public function &__isset($name) {
      return $this->__get($name);
    }

    public function __set($name, $value) {
      trigger_error('Setting data is prohibited ('.$name.')', E_USER_WARNING);
    }

    private function _load($field) {

      switch($field) {

        case 'description':
        case 'short_description':
        case 'head_title':
        case 'meta_description':
        case 'h1_title':
        case 'link':

          $this->_data['info'] = [];

          $query = database::query(
            "select * from ". DB_PREFIX ."brands_info
            where brand_id = ". (int)$this->_data['id'] ."
            and language_code in ('". implode("', '", database::input($this->_language_codes)) ."')
            order by field(language_code, '". implode("', '", database::input($this->_language_codes)) ."');"
          );

          while ($row = database::fetch($query)) {
            foreach ($row as $key => $value) {
              if (in_array($key, ['id', 'brand_id', 'language_code'])) continue;
              if (empty($this->_data[$key])) $this->_data[$key] = $value;
            }
          }

          break;

        default:

          $query = database::query(
            "select * from ". DB_PREFIX ."brands
            where id = ". (int)$this->_data['id'] ."
            limit 1;"
          );

          if (!$row = database::fetch($query)) return;

          foreach ($row as $key => $value) {
            switch($key) {
              case 'keywords':
                $row[$key] = explode(',', $row[$key]);
                break;

              default:
                $this->_data[$key] = $value;
                break;
            }
          }

          break;
      }
    }
  }
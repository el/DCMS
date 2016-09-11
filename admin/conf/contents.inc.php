<?php 
$contents = array (
  'cfirmalar' => 
  array (
    'name' => 'Firmalar',
    'db' => 'cfirmalar',
    'type' => '0',
    'list' => 'iname',
    'divider' => '',
    'icon' => 'icon-align-left',
    'connect' => '',
    'connected' => '',
    'parts' => 
    array (
      'iname' => 
      array (
        'name' => 'Adı',
        'db' => 'iname',
        'type' => 'text',
        'bound' => 'users',
      ),
      'itam_adi' => 
      array (
        'name' => 'Tam Adı',
        'db' => 'itam_adi',
        'type' => 'text',
        'bound' => 'users',
      ),
      'iadresi' => 
      array (
        'name' => 'Adresi',
        'db' => 'iadresi',
        'type' => 'text',
        'bound' => 'users',
      ),
      'ivergi_dairesi' => 
      array (
        'name' => 'Vergi Dairesi',
        'db' => 'ivergi_dairesi',
        'type' => 'text',
        'bound' => 'users',
      ),
      'ivergi_no' => 
      array (
        'name' => 'Vergi No',
        'db' => 'ivergi_no',
        'type' => 'text',
        'bound' => 'users',
      ),
      'itelefonu' => 
      array (
        'name' => 'Telefonu',
        'db' => 'itelefonu',
        'type' => 'text',
        'bound' => 'users',
      ),
    ),
  ),
  'cgorevler' => 
  array (
    'name' => 'Görevler',
    'db' => 'cgorevler',
    'type' => '2',
    'list' => 'iname',
    'divider' => '',
    'icon' => 'icon-bar-chart',
    'connect' => '',
    'connected' => '',
    'actions' => 
    array (
    ),
    'multi-language' => false,
    'parts' => 
    array (
      'iname' => 
      array (
        'name' => 'Adı',
        'db' => 'iname',
        'type' => 'text',
        'bound' => 'users',
      ),
      'ibaslangic' => 
      array (
        'name' => 'Başlangıç',
        'db' => 'ibaslangic',
        'type' => 'date',
        'bound' => 'users',
      ),
      'ibitis' => 
      array (
        'name' => 'Bitiş',
        'db' => 'ibitis',
        'type' => 'date',
        'bound' => 'users',
      ),
      'itekrar' => 
      array (
        'name' => 'Tekrar',
        'db' => 'itekrar',
        'type' => 'text',
        'bound' => 'users',
      ),
      'ikullanici' => 
      array (
        'name' => 'Kullanıcı',
        'db' => 'ikullanici',
        'type' => 'mbound',
        'bound' => 'users',
      ),
    ),
  ),
);

<?php 
$parts = array (
  'files' => 
  array (
    'name' => 'Dosya Yönetimi',
    'db' => 'files',
    'icon' => 'icon-file-text',
    'divider' => '',
    'disabled' => false,
  ),
  'users' => 
  array (
    'name' => 'Kullanıcı Hesap Yönetimi',
    'db' => 'users',
    'icon' => 'icon-group',
    'divider' => '',
    'disabled' => false,
  ),
  'apps' => 
  array (
    'name' => 'Uygulama Özellikleri',
    'db' => 'apps',
    'icon' => 'icon-cloud',
    'parts' => 
    array (
      'iname' => 
      array (
        'name' => 'Adı',
        'db' => 'iname',
        'type' => 'text',
        'bound' => 'users',
      ),
      'iayarlar' => 
      array (
        'name' => 'Ayarlar',
        'db' => 'iayarlar',
        'type' => 'checkfrom',
        'bound' => 'users',
        'options' => 
        array (
          0 => 'test',
          1 => 'deneme',
        ),
      ),
      'imount' => 
      array (
        'name' => 'Icecast Mount',
        'db' => 'imount',
        'type' => 'admin-text',
        'bound' => 'users',
      ),
      'iport' => 
      array (
        'name' => 'MPD Port',
        'db' => 'iport',
        'type' => 'admin-number',
        'bound' => 'users',
      ),
    ),
    'divider' => '',
    'disabled' => false,
  ),
  'logs' => 
  array (
    'name' => 'Hata Kayıtları',
    'db' => 'logs',
    'icon' => 'icon-warning-sign',
    'divider' => '',
    'disabled' => false,
  ),
  'admin' => 
  array (
    'name' => 'Bölüm Yönetimi',
    'db' => 'admin',
    'icon' => 'icon-th-list',
    'divider' => '',
    'disabled' => false,
  ),
  'conf' => 
  array (
    'name' => 'Sistem Ayarları',
    'db' => 'conf',
    'icon' => 'icon-cogs',
    'divider' => '',
    'disabled' => false,
  ),
  'bills' => 
  array (
    'name' => 'Faturalar',
    'db' => 'bills',
    'icon' => 'icon-money',
    'divider' => '',
    'disabled' => false,
    'settings' => 
    array (
      'connect' => 'cfirmalar',
      'full_name' => 'itam_adi',
      'taxplace' => 'ivergi_dairesi',
      'taxnum' => 'ivergi_no',
      'address' => 'iadresi',
      'phone' => 'itelefonu',
      'currency' => 'TL',
    ),
  ),
  'settings' => 
  array (
    'name' => 'Özellikler',
    'db' => 'settings',
    'icon' => 'icon-dashboard',
    'divider' => '',
    'disabled' => false,
  ),
  'flows' => 
  array (
    'name' => 'Süreç Yönetimi',
    'db' => 'flows',
    'icon' => 'icon-random',
    'divider' => '',
    'disabled' => false,
  ),
  'reports' => 
  array (
    'name' => 'Rapor Sihirbazı',
    'db' => 'reports',
    'icon' => 'icon-bar-chart',
    'divider' => '',
    'disabled' => false,
  ),
  'forms' => 
  array (
    'name' => 'Form Sihirbazı',
    'db' => 'forms',
    'icon' => 'icon-edit',
    'divider' => '',
    'disabled' => false,
  ),
  'google' => 
  array (
    'name' => 'Google',
    'db' => 'google',
    'icon' => 'icon-google-plus',
    'divider' => '',
    'settings' => 
    array (
      'clientid' => '108748259853-2q55d8g2ej90m94bd1hbmu4kjq0jb4qo.apps.googleusercontent.com',
      'clientsecret' => '0IYZcQn9rJZyK8P1gUA3PVt_',
      'developerkey' => 'AIzaSyAn951PVBehj6sI_7pkUwMvIrKOk7DS7wE',
    ),
    'parts' => 
    array (
      'calendar' => 
      array (
        'db' => 'cshowlar',
        'event' => 'iname',
        'start' => 'ibaslangic',
        'end' => 'ibitis',
        'description' => '',
        'location' => '',
        'users' => '',
      ),
      'contacts' => 
      array (
        'db' => '',
        'name' => '',
        'email' => '',
        'phone' => '',
        'address' => '',
        'organization' => '',
      ),
    ),
    'disabled' => false,
  ),
  'calendar' => 
  array (
    'name' => 'Takvim',
    'db' => 'calendar',
    'icon' => 'icon-calendar',
    'divider' => '',
    'settings' => 
    array (
      'connect' => 'cgorevler',
      'start' => 'ibaslangic',
      'end' => 'ibitis',
      'repeat' => 'itekrar',
      'users' => 'ikullanici',
      'minutes' => '10',
      'minTime' => '0',
      'maxTime' => '24',
      'resource' => '',
      'dropable' => true,
    ),
    'disabled' => false,
  ),
);


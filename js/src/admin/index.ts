import app from 'flarum/admin/app';

app.initializers.add('flarum-whmcs', () => {
  app.extensionData
    .for('delzyioncloud-whmcs')
    .registerSetting({
      setting: 'delzyion-flarum-whmcs.whmcs_url',
      type: 'url',
      placeholder: 'https://www.example.com/whmcs',
      label: app.translator.trans('delzyioncloud.flarum-whmcs.admin.settings.whmcs_url'),
    })
    .registerSetting({
      setting: 'delzyion-flarum-whmcs.client_id',
      type: 'text',
      placeholder: 'WHMCS-DEMO./hW6JgfqRfZ8eCCIsZHQTg==',
      label: app.translator.trans('delzyioncloud.flarum-whmcs.admin.settings.client_id'),
    })
    .registerSetting({
      setting: 'delzyion-flarum-whmcs.client_secret',
      type: 'password',
      label: app.translator.trans('delzyioncloud.flarum-whmcs.admin.settings.client_secret'),
    })
});

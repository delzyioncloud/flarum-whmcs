import { extend } from 'flarum/common/extend';
import app from 'flarum/forum/app';
import LogInButtons from 'flarum/forum/components/LogInButtons';
import LogInButton from 'flarum/forum/components/LogInButton';

app.initializers.add('flarum-whmcs', () => {
  extend(LogInButtons.prototype, 'items', function(items) {
    items.add(
      'whmcs',
      LogInButton.component(
        {
          className: 'Button LogInButton--whmcs',
          icon: 'fas fa-cog',
          path: '/auth/whmcs'
        },
        app.translator.trans('delzyioncloud.flarum-whmcs.forum.button.login')
      )
    );
  });
});

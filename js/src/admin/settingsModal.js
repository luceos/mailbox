import { settings } from '@fof-components';

const {
  SettingsModal,
  items: { BooleanItem },
} = settings;

export default function () {
  app.extensionSettings['fof-mailbox'] = () =>
    app.modal.show(
      new SettingsModal({
        title: app.translator.trans('fof-mailbox.admin.settings.title'),
        type: 'small',
        items: [
          <BooleanItem key="fof-mailbox.modes.account_per_primary_tag">
            {app.translator.trans('fof-mailbox.admin.settings.modes.account_per_primary_tag')}
          </BooleanItem>
        ],
      })
    );
}

import {extend} from "flarum/extend";
import EditTagModal from "flarum/tags/components/EditTagModal";

export default function () {
  extend(EditTagModal.prototype, 'init', function() {
    this.mailboxEnabled = m.prop(this.tag.mailboxEnabled() || false);
    this.mailboxSender = m.prop(this.tag.mailboxSender() || '');
    this.mailboxImapHost = m.prop(this.tag.mailboxImapHost() || '');
    this.mailboxImapPort = m.prop(this.tag.mailboxImapPort() || '');
    this.mailboxSmtpPort = m.prop(this.tag.mailboxSmtpPort() || '');
    this.mailboxImapEncryption = m.prop(this.tag.mailboxImapEncryption() || '');
    this.mailboxImapUsername = m.prop(this.tag.mailboxImapUsername() || '');
    this.mailboxImapPassword = m.prop(this.tag.mailboxImapPassword() || '');
  });

  extend(EditTagModal.prototype, 'submitData', function(data) {
    data.mailboxEnabled = this.mailboxEnabled();
    data.mailboxSender = this.mailboxSender();
    data.mailboxImapHost = this.mailboxImapHost();
    data.mailboxImapPort = this.mailboxImapPort();
    data.mailboxSmtpPort = this.mailboxSmtpPort();
    data.mailboxImapEncryption = this.mailboxImapEncryption();
    data.mailboxImapUsername = this.mailboxImapUsername();
    data.mailboxImapPassword = this.mailboxImapPassword();

    return data;
  });
  extend(EditTagModal.prototype, 'fields', function(fields) {
    if (this.tag.canEnableMailbox()) {
      fields.add('mailbox_enabled', <div className="Form-group">
        <div>
          <label className="checkbox">
            <input type="checkbox" value="1" checked={this.mailboxEnabled()} onchange={m.withAttr('checked', this.mailboxEnabled)}/>
            {app.translator.trans('fof-mailbox.admin.edit_tag.mailbox_enabled')}
          </label>
        </div>

        <p>
          {app.translator.trans('fof-mailbox.admin.edit_tag.mailbox_consequences')}
        </p>
      </div>, 9);
    }

    if (this.tag.canEnableMailbox() && !! this.mailboxEnabled()) {
      fields.add('mailbox_sender', <div className="Form-group">
        <label>{app.translator.trans('fof-mailbox.admin.edit_tag.mailbox_sender')}</label>
        <input className="FormControl" value={this.mailboxSender()} onInput={m.withAttr('value', this.mailboxSender)}/>
      </div>, 9);
      fields.add('mailbox_imap_host', <div className="Form-group">
        <label>{app.translator.trans('fof-mailbox.admin.edit_tag.mailbox_imap_host')}</label>
        <input className="FormControl" value={this.mailboxImapHost()} onInput={m.withAttr('value', this.mailboxImapHost)}/>
      </div>, 9);
      fields.add('mailbox_imap_port', <div className="Form-group">
        <label>{app.translator.trans('fof-mailbox.admin.edit_tag.mailbox_imap_port')}</label>
        <input className="FormControl" value={this.mailboxImapPort()} onInput={m.withAttr('value', this.mailboxImapPort)}/>
      </div>, 9);
      fields.add('mailbox_smtp_port', <div className="Form-group">
        <label>{app.translator.trans('fof-mailbox.admin.edit_tag.mailbox_smtp_port')}</label>
        <input className="FormControl" value={this.mailboxSmtpPort()} onInput={m.withAttr('value', this.mailboxSmtpPort)}/>
      </div>, 9);
      fields.add('mailbox_imap_encryption', <div className="Form-group">
        <label>{app.translator.trans('fof-mailbox.admin.edit_tag.mailbox_imap_encryption')}</label>
        <input className="FormControl" value={this.mailboxImapEncryption()} onInput={m.withAttr('value', this.mailboxImapEncryption)}/>
      </div>, 9);
      fields.add('mailbox_imap_username', <div className="Form-group">
        <label>{app.translator.trans('fof-mailbox.admin.edit_tag.mailbox_imap_username')}</label>
        <input className="FormControl" value={this.mailboxImapUsername()} onInput={m.withAttr('value', this.mailboxImapUsername)}/>
      </div>, 9);
      fields.add('mailbox_imap_password', <div className="Form-group">
        <label>{app.translator.trans('fof-mailbox.admin.edit_tag.mailbox_imap_password')}</label>
        <input className="FormControl" value={this.mailboxImapPassword()} onInput={m.withAttr('value', this.mailboxImapPassword)}/>
      </div>, 9);
    }

    return fields;
  })
}

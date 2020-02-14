import Model from "flarum/Model";
import Tag from 'flarum/tags/models/Tag';
import computed from 'flarum/utils/computed';

export default function () {
  Tag.prototype.canEnableMailbox = computed('isPrimary', (isPrimary) => "fof-mailbox.modes.account_per_primary_tag" in app.data.settings && !!app.data.settings["fof-mailbox.modes.account_per_primary_tag"] && isPrimary);
  Tag.prototype.mailboxEnabled = Model.attribute('mailboxEnabled');

  Tag.prototype.mailboxImapHost = Model.attribute('mailboxImapHost');
  Tag.prototype.mailboxImapPort = Model.attribute('mailboxImapPort');
  Tag.prototype.mailboxImapUsername = Model.attribute('mailboxImapUsername');
  Tag.prototype.mailboxImapPassword = Model.attribute('mailboxImapPassword');
}

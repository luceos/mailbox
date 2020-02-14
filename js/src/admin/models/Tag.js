import Model from "flarum/Model";
import Tag from 'flarum/tags/models/Tag';

export default function () {
  Tag.prototype.mailboxEnabled = Model.attribute('mailboxEnabled');

  Tag.prototype.mailboxImapHost = Model.attribute('mailboxImapHost');
  Tag.prototype.mailboxImapPort = Model.attribute('mailboxImapPort');
  Tag.prototype.mailboxImapUsername = Model.attribute('mailboxImapUsername');
  Tag.prototype.mailboxImapPassword = Model.attribute('mailboxImapPassword');
}

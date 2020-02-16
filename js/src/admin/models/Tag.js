import Model from "flarum/Model";
import Tag from 'flarum/tags/models/Tag';
import computed from 'flarum/utils/computed';

export default function () {
  Tag.prototype.canEnableMailbox = computed('exists', (exists) => exists);
  Tag.prototype.mailboxEnabled = Model.attribute('mailbox_enabled');

  Tag.prototype.mailboxSender = Model.attribute('mailbox_sender');
  Tag.prototype.mailboxImapHost = Model.attribute('mailbox_imap_host');
  Tag.prototype.mailboxImapPort = Model.attribute('mailbox_imap_port');
  Tag.prototype.mailboxSmtpPort = Model.attribute('mailbox_smtp_port');
  Tag.prototype.mailboxImapEncryption = Model.attribute('mailbox_imap_encryption');
  Tag.prototype.mailboxImapUsername = Model.attribute('mailbox_imap_username');
  Tag.prototype.mailboxImapPassword = Model.attribute('mailbox_imap_password');
}

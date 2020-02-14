import settingsModal from './settingsModal';
import editTagModalFields from "./extends/editTagModalFields";
import Tag from "./models/Tag";

app.initializers.add('fof/mailbox', () => {
  // Settings Modal
  settingsModal();

  // Enable based on settings.
  const settings = app.data.settings;

  const enabledPerTag = "fof-mailbox.modes.account_per_primary_tag" in settings && !!settings["fof-mailbox.modes.account_per_primary_tag"]

  if (enabledPerTag) {
    Tag();
    editTagModalFields();
  }
});

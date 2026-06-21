import { CmsEditorManager } from './cms_editor.js';

const manager = new CmsEditorManager(window.CmsPageEditor || {});
manager.init();

window.CmsEditor = manager;

import { HeadingSchema, HeadingBlock } from './blocks/heading.js';
import { ParagraphSchema, ParagraphBlock } from './blocks/paragraph.js';
import { QuoteSchema, QuoteBlock } from './blocks/quote.js';
import { ImageSchema, ImageBlock } from './blocks/image.js';
import { ListSchema, ListBlock } from './blocks/list.js';
import { TableSchema, TableBlock } from './blocks/table.js';
import { registry } from "./block_registry.js";
import { EditorManager } from "./editor.js";

registry.register(HeadingSchema, HeadingBlock);
registry.register(ParagraphSchema, ParagraphBlock);
registry.register(QuoteSchema, QuoteBlock);
registry.register(ImageSchema, ImageBlock);
registry.register(ListSchema, ListBlock);
registry.register(TableSchema, TableBlock);

const manager = new EditorManager();
manager.init();

window.BeEditor = manager;
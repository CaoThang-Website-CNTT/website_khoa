import { HeadingSchema, HeadingBlock } from './blocks/heading.js';
import { ParagraphSchema, ParagraphBlock } from './blocks/paragraph.js';
import { QuoteSchema, QuoteBlock } from './blocks/quote.js';
import { registry } from "./block_registry.js";
import { EditorManager } from "./editor.js";

registry.register(HeadingSchema, HeadingBlock);
registry.register(ParagraphSchema, ParagraphBlock);
registry.register(QuoteSchema, QuoteBlock);

const manager = new EditorManager();
manager.init();

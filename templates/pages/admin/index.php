<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DnD.js Demo</title>
    <style>
        :root {
            --primary: #4f46e5;
            --bg-color: #f3f4f6;
            --item-bg: #ffffff;
            --border: #e5e7eb;
            --text-main: #1f2937;
            --text-muted: #6b7280;
        }

        body {
            font-family: system-ui, -apple-system, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            margin: 0;
            padding: 2rem;
        }

        h1, h2 {
            text-align: center;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .section {
            background: #fff;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .section h3 {
            margin-top: 0;
            color: var(--primary);
            border-bottom: 2px solid var(--bg-color);
            padding-bottom: 0.5rem;
        }

        /* Generic List Style */
        .list {
            list-style: none;
            padding: 0;
            margin: 0;
            min-height: 50px; /* For empty lists */
            background: var(--bg-color);
            border-radius: 6px;
            padding: 0.5rem;
        }

        .item {
            background: var(--item-bg);
            border: 1px solid var(--border);
            padding: 0.75rem 1rem;
            margin-bottom: 0.5rem;
            border-radius: 4px;
            cursor: grab;
            user-select: none;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
        }
        
        .item:last-child {
            margin-bottom: 0;
        }

        /* Kanban Layout (Multi-list) */
        .kanban-board {
            display: flex;
            gap: 1rem;
        }
        .kanban-board > div {
            flex: 1;
        }

        /* Nested List */
        .nested-list {
            min-height: 30px;
            padding-left: 1.5rem;
            margin-top: 0.5rem;
            background: rgba(0,0,0,0.03);
            border: 1px dashed var(--border);
        }

        /* Handle Only */
        .drag-handle {
            cursor: grab;
            margin-right: 1rem;
            color: var(--text-muted);
            font-weight: bold;
            font-size: 1.2rem;
        }
        .handle-item {
            cursor: default; /* Overriding default grab */
        }

        /* Grid Layout */
        .grid-layout {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            background: var(--bg-color);
            padding: 1rem;
            border-radius: 6px;
        }
        .grid-item {
            background: var(--primary);
            color: white;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            cursor: grab;
            font-weight: bold;
        }

        /* DnD Classes */
        .dnd-ghost {
            opacity: 0.4;
            background: #e0e7ff;
        }
        .dnd-chosen {
            box-shadow: 0 0 0 2px var(--primary);
        }
        .highlight {
            background-color: #fef08a !important;
            border-color: #eab308 !important;
        }

        /* Custom Drop CSS */
        .custom-dropped-item {
            background-color: #dcfce7;
            border-color: #22c55e;
            color: #166534;
        }

        /* Logger */
        .logger {
            grid-column: 1 / -1;
            background: #1e293b;
            color: #10b981;
            padding: 1rem;
            border-radius: 8px;
            font-family: monospace;
            height: 150px;
            overflow-y: auto;
        }
    </style>
</head>
<body>

    <h1>DnD.js Feature Demo</h1>

    <div class="container">
        
        <div class="section">
            <h3>1. Same-list Sort</h3>
            <ul id="same-list" class="list">
                <li class="item" data-id="1">Item 1</li>
                <li class="item" data-id="2">Item 2</li>
                <li class="item" data-id="3">Item 3</li>
                <li class="item" data-id="4">Item 4</li>
            </ul>
        </div>

        <div class="section">
            <h3>2. Multi-list (Grouped)</h3>
            <div class="kanban-board">
                <div>
                    <strong>Todo</strong>
                    <ul id="list-a" class="list">
                        <li class="item" data-id="t1">Task A</li>
                        <li class="item" data-id="t2">Task B</li>
                    </ul>
                </div>
                <div>
                    <strong>Done</strong>
                    <ul id="list-b" class="list">
                        <li class="item" data-id="t3">Task C</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="section">
            <h3>3. Nested Lists</h3>
            <ul class="list nested-list">
                <li class="item" data-id="n1">
                    <div>Parent 1</div>
                    <ul class="list nested-list">
                        <li class="item" data-id="n1-1">Child 1.1</li>
                        <li class="item" data-id="n1-2">Child 1.2</li>
                    </ul>
                </li>
                <li class="item" data-id="n2">
                    <div>Parent 2</div>
                    <ul class="list nested-list">
                        <li class="item" data-id="n2-1">Child 2.1</li>
                    </ul>
                </li>
            </ul>
        </div>

        <div class="section">
            <h3>4. Handle Only</h3>
            <ul id="handle-list" class="list">
                <li class="item handle-item" data-id="h1">
                    <span class="drag-handle">☰</span> Content A
                </li>
                <li class="item handle-item" data-id="h2">
                    <span class="drag-handle">☰</span> Content B
                </li>
                <li class="item handle-item" data-id="h3">
                    <span class="drag-handle">☰</span> Content C
                </li>
            </ul>
        </div>

        <div class="section">
            <h3>5. Swap Mode</h3>
            <ul id="swap-list" class="list">
                <li class="item" data-id="s1">Swap Item 1</li>
                <li class="item" data-id="s2">Swap Item 2</li>
                <li class="item" data-id="s3">Swap Item 3</li>
            </ul>
        </div>

        <div class="section">
            <h3>6. Grid Sort (Auto-dir)</h3>
            <div id="grid-list" class="grid-layout">
                <div class="grid-item" data-id="g1">1</div>
                <div class="grid-item" data-id="g2">2</div>
                <div class="grid-item" data-id="g3">3</div>
                <div class="grid-item" data-id="g4">4</div>
                <div class="grid-item" data-id="g5">5</div>
                <div class="grid-item" data-id="g6">6</div>
            </div>
        </div>

        <div class="section">
            <h3>8. Custom Drop (Clone & Transform)</h3>
            <div class="kanban-board">
                <div>
                    <strong>Source (Stays Intact)</strong>
                    <ul id="clone-source" class="list">
                        <li class="item" data-id="c1">Component A</li>
                        <li class="item" data-id="c2">Component B</li>
                    </ul>
                </div>
                <div>
                    <strong>Canvas (Receives Custom DOM)</strong>
                    <ul id="clone-dest" class="list">
                        </ul>
                </div>
            </div>
        </div>

        <div class="section logger" id="logger">
            <div>// Event Monitor Output...</div>
        </div>

    </div>

    <script src="<?= url('/public/js/dnd_v2.js') ?>"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const logger = document.getElementById('logger');
            function logEvent(msg) {
                const div = document.createElement('div');
                div.textContent = `> ${msg}`;
                logger.prepend(div);
            }

            // 1. Same-list
            new DnD(document.getElementById('same-list'), { 
                animation: 150 
            });

            // 2. Multi-list (both lists use same group name)
            new DnD(document.getElementById('list-a'), { group: 'kanban', animation: 150 });
            new DnD(document.getElementById('list-b'), { group: 'kanban', animation: 150 });

            // 3. Nested
            document.querySelectorAll('.nested-list').forEach(el => {
                new DnD(el, { group: 'nested', animation: 150 })
            });

            // 4. Handle only
            new DnD(document.getElementById('handle-list'), { 
                handle: '.drag-handle', 
                ghostHandle: true,
                animation: 150 
            });

            // 5. Swap mode
            new DnD(document.getElementById('swap-list'), { 
                swap: true, 
                swapClass: 'highlight', 
                animation: 150 
            });

            // 6. Grid
            new DnD(document.getElementById('grid-list'), { 
                direction: 'auto', 
                animation: 150 
            });

            // 8. Custom Drop (Clone & Transform)
            new DnD(document.getElementById('clone-source'), {
                group: 'clone-group',
                animation: 150,
                onEnd: ({ item, from, to, oldIndex }) => {
                    // Only apply custom logic if dragged to a different list
                    if (from !== to) {
                        logEvent(`[Custom Drop] Creating new DOM element for ${item.textContent.trim()}`);
                        
                        // 1. Create the custom item
                        const newCustomItem = document.createElement('li');
                        newCustomItem.className = 'item custom-dropped-item';
                        newCustomItem.dataset.id = 'custom-' + Date.now();
                        newCustomItem.innerHTML = `✨ Built from: ${item.textContent}`;

                        // 2. Replace the dragged item sitting in the destination
                        item.replaceWith(newCustomItem);

                        // 3. Put the original item back where it started in the source list
                        const referenceNode = from.children[oldIndex];
                        if (referenceNode) {
                            from.insertBefore(item, referenceNode);
                        } else {
                            from.appendChild(item);
                        }
                    }
                }
            });

            new DnD(document.getElementById('clone-dest'), {
                group: 'clone-group',
                animation: 150
            });

            // 7. Global Monitor Setup
            DnDMonitor.on('dragend', ({ item, from, to, oldIndex, newIndex, canceled }) => {
                if (canceled) {
                    logEvent(`[Global Monitor] Drag canceled for ID: ${item.dataset.id || 'unknown'}`);
                    return;
                }
                const status = (from === to) ? 'sorted' : 'moved';
                logEvent(`[Global Monitor] Item '${item.dataset.id || 'custom'}' ${status}.`);
            });
        });
    </script>
</body>
</html>
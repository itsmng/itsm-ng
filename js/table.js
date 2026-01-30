(function (window, document) {
    const existingQueue = Array.isArray(window.ITSMTableQueue) ? window.ITSMTableQueue : [];

    function initTable(config) {
        if (!config || !config.id) {
            return;
        }

        const wrapperElement = document.getElementById(config.id);
        if (!wrapperElement) {
            return;
        }

        if (wrapperElement.dataset.tableInitialized === 'true') {
            return;
        }
        wrapperElement.dataset.tableInitialized = 'true';

        const TableCore = window.TableCore;
        const Nanostores = window.Nanostores;
        const htm = window.htm;
        const vhtml = window.vhtml;

        if (!TableCore || !Nanostores || !htm || !vhtml) {
            console.error('Table dependencies missing.');
            return;
        }

        let massiveActionSelection = [];
        window[config.id + '_getMassiveActionSelection'] = function () {
            return massiveActionSelection;
        };

        const { createColumnHelper, createTable, getCoreRowModel, getPaginationRowModel, getSortedRowModel } = TableCore;
        const { atom } = Nanostores;
        const html = htm.bind(vhtml);
        const stateStore = atom({});
        const trimmedId = String(config.id).replace(/\d+$/, '');
        const fieldsArray = Array.isArray(config.fields) ? config.fields : Object.entries(config.fields || {});
        const fields = Object.fromEntries(fieldsArray);
        const fieldKeys = fieldsArray.map(pair => pair[0]);

        const values = config.values || [];
        const massiveAction = config.massiveAction || [];
        const radio = !!config.radio;
        const columnEdit = !!config.columnEdit;
        const minimal = !!config.minimal;
        const isTrash = !!config.isTrash;
        const canTrash = !!config.canTrash;
        const noToolBar = !!config.noToolBar;
        const url = config.url || null;
        const itemtype = config.itemtype || '';
        const showExport = config.showExport !== false;
        const hasMassiveAction = !!config.hasMassiveAction;
        const pageSize = config.pageSize || 15;
        const toolbarId = 'toolbar' + (config.rand || Math.floor(Math.random() * 1000000));

        if (!url && hasMassiveAction) {
            Object.keys(values).forEach(key => {
                if (values[key]) {
                    values[key].value = massiveAction[key];
                }
            });
        }

        let table;
        let serverTotal = null;

        const htmlInjectionMap = new Map();
        function decodeHtml(htmlString) {
            const txt = document.createElement('textarea');
            txt.innerHTML = htmlString;
            return txt.value;
        }

        const processSpecialCharacters = (value) => {
            if (typeof value !== 'string') return value;
            return value
                .replace(/\$\$##\$\$/g, '<hr>')
                .replace(/\$#\$/g, ' ')
                .replace(/#LBBR#/g, '<br>')
                .replace(/#LBHR#/g, '<hr>');
        };

        const renderRawHTML = (htmlString) => {
            const processed = processSpecialCharacters(htmlString);
            if (typeof processed === 'string' && /<[^>]*>/.test(processed)) {
                const marker = `__HTML_CONTENT_${Math.random().toString(36).substring(2, 10)}__`;
                htmlInjectionMap.set(marker, processed);
                return marker;
            }
            return decodeHtml(processed);
        };

        const flexRender = (comp, props) => (typeof comp === 'function' ? comp(props) : comp);

        const storeState = (state) => {
            const keySuffix = itemtype || trimmedId;
            localStorage.setItem('tableState_pagination_' + keySuffix, JSON.stringify(state.pagination));
            localStorage.setItem('tableState_sorting_' + keySuffix, JSON.stringify(state.sorting));
            localStorage.setItem('tableState_visibility_' + keySuffix, JSON.stringify(state.columnVisibility));
        };

        const loadState = () => {
            const keySuffix = itemtype || trimmedId;
            const pagination = JSON.parse(localStorage.getItem('tableState_pagination_' + keySuffix) || 'null');
            const sorting = JSON.parse(localStorage.getItem('tableState_sorting_' + keySuffix) || 'null');
            const visibility = JSON.parse(localStorage.getItem('tableState_visibility_' + keySuffix) || 'null');
            return { pagination, sorting, visibility };
        };

        const validatePaginationState = (totalRows) => {
            const state = table.getState();
            const { pageIndex, pageSize: currentPageSize } = state.pagination;
            const maxPageIndex = Math.max(0, Math.ceil(totalRows / currentPageSize) - 1);

            if (pageIndex > maxPageIndex) {
                table.setPageIndex(0);
                return true;
            }
            return false;
        };

        const renderLoading = (isLoading) => {
            const tableContainer = wrapperElement.querySelector('.fixed-table-container');
            if (!tableContainer) {
                return;
            }
            let overlay = tableContainer.querySelector('.loading-overlay');
            if (isLoading) {
                if (!overlay) {
                    overlay = document.createElement('div');
                    overlay.className = 'loading-overlay';
                    overlay.style.position = 'absolute';
                    overlay.style.top = '0';
                    overlay.style.left = '0';
                    overlay.style.width = '100%';
                    overlay.style.height = '100%';
                    overlay.style.background = 'rgba(255, 255, 255, 0.8)';
                    overlay.style.display = 'flex';
                    overlay.style.alignItems = 'center';
                    overlay.style.justifyContent = 'center';
                    overlay.style.zIndex = '10';
                    overlay.innerHTML = '<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>';
                    tableContainer.style.position = 'relative';
                    tableContainer.appendChild(overlay);
                }
            } else if (overlay) {
                tableContainer.removeChild(overlay);
            }
        };

        const fetchDataAndRender = async () => {
            if (!url) return;
            renderLoading(true);

            const state = table.getState();
            const { pageIndex, pageSize: currentPageSize } = state.pagination;
            const { sorting } = state;

            const fetchUrl = new URL(url.replace(/&amp;/g, '&'), window.location.origin);
            fetchUrl.searchParams.set('offset', pageIndex * currentPageSize);
            fetchUrl.searchParams.set('limit', currentPageSize);

            if (sorting && sorting.length > 0) {
                fetchUrl.searchParams.set('sort', sorting[0].id);
                fetchUrl.searchParams.set('order', sorting[0].desc ? 'desc' : 'asc');
            }

            try {
                const response = await fetch(fetchUrl.toString());
                const result = await response.json();
                serverTotal = result.total;
                table.setOptions(prev => ({
                    ...prev,
                    data: result.rows,
                    pageCount: Math.ceil(result.total / currentPageSize),
                }));
                if (validatePaginationState(serverTotal)) {
                    renderLoading(false);
                    fetchDataAndRender();
                    return;
                }
            } catch (error) {
                console.error('Failed to fetch table data:', error);
                table.setOptions(prev => ({ ...prev, data: [] }));
                serverTotal = 0;
            } finally {
                renderLoading(false);
                renderTable();
            }
        };

        const useTable = (options) => {
            const { data, ...restOptions } = options;
            const resolvedOptions = {
                state: {},
                onStateChange: () => {},
                getCoreRowModel: getCoreRowModel(),
                getPaginationRowModel: getPaginationRowModel(),
                getSortedRowModel: getSortedRowModel(),
                data,
                ...restOptions,
            };
            const { pagination, sorting, visibility } = loadState();
            if (pagination) resolvedOptions.initialState.pagination = pagination;
            if (sorting) resolvedOptions.initialState.sorting = sorting;
            if (visibility) resolvedOptions.initialState.columnVisibility = visibility;

            const tableInstance = createTable(resolvedOptions);
            stateStore.set(tableInstance.initialState);

            stateStore.subscribe((currentState) => {
                tableInstance.setOptions((prev) => ({
                    ...prev,
                    ...restOptions,
                    state: { ...currentState, ...restOptions.state },
                    onStateChange: (updater) => {
                        let newState = typeof updater === 'function' ? updater(currentState) : updater;
                        const oldState = currentState;

                        if (url) {
                            const paginationChanged = JSON.stringify(oldState.pagination) !== JSON.stringify(newState.pagination);
                            const sortingChanged = JSON.stringify(oldState.sorting) !== JSON.stringify(newState.sorting);
                            if (paginationChanged || sortingChanged) {
                                if (paginationChanged) massiveActionSelection = [];
                                stateStore.set(newState);
                                storeState(newState);
                                fetchDataAndRender();
                                return;
                            }
                        }
                        stateStore.set(newState);
                        storeState(newState);
                        renderTable();
                    },
                }));
            });
            return tableInstance;
        };

        const columnHelper = createColumnHelper();
        let columns = [];
        if (radio) {
            columns.push(columnHelper.display({
                id: 'select',
                cell: info => html`<input type="radio" name="row-select-${config.id}" class="row-select" data-row-id="${info.row.id}" />`,
            }));
        } else if (hasMassiveAction) {
            columns.push(columnHelper.display({
                id: 'select',
                header: () => html`<input type="checkbox" id="select-all" />`,
                cell: info => html`<input type="checkbox" class="row-select" data-row-id="${info.row.id}" />`,
            }));
        }
        const dataColumns = fieldKeys.map(field => columnHelper.accessor(String(field), {
            header: () => html`${renderRawHTML(fields[field])}`,
            cell: info => renderRawHTML(info.getValue() ?? ''),
        }));
        columns = columns.concat(dataColumns);

        const data = Object.values(values).map(value => {
            const row = {};
            fieldKeys.forEach(field => {
                row[String(field)] = value[String(field)];
            });
            if (value && value.value) {
                row.value = value.value;
            }
            return row;
        });

        table = useTable({
            data,
            columns,
            manualPagination: !!url,
            manualSorting: !!url,
            initialState: {
                pagination: {
                    pageSize,
                },
            },
        });

        const renderPageSizeDropdown = () => {
            const currentPageSize = table.getState().pagination.pageSize;
            const pageSizeOptions = [15, 25, 50, 100, 1000, 10000];

            return html`
                <div class="page-list">
                    <div class="dropdown dropup">
                        <button
                            class="btn btn-secondary dropdown-toggle"
                            type="button"
                            data-bs-toggle="dropdown"
                        >
                            ${currentPageSize}
                        </button>
                        <div class="dropdown-menu">
                            ${pageSizeOptions.map(size => html`
                                <a
                                    class="dropdown-item"
                                    onclick="table.setPageSize(${size})"
                                >
                                    ${size}
                                </a>
                            `)}
                        </div>
                    </div>
                </div>
            `;
        };

        const renderPageSizeSelector = () => {
            const currentPageSize = table.getState().pagination.pageSize;
            const totalRows = url ? serverTotal : table.getFilteredRowModel().rows.length;

            return html`
                <div class="fixed-table-pagination">
                    <div class="float-left pagination-detail">
                        <span class="pagination-info">
                            Showing ${Math.min(totalRows, currentPageSize)} of ${totalRows} entries
                        </span>
                        ${renderPageSizeDropdown()}
                    </div>
                </div>
            `;
        };

        const renderPagination = () => {
            const totalPages = table.getPageCount();
            const currentPage = table.getState().pagination.pageIndex + 1;
            const currentPageSize = table.getState().pagination.pageSize;
            const totalRows = url ? serverTotal : table.getFilteredRowModel().rows.length;
            const startRow = table.getState().pagination.pageIndex * currentPageSize + 1;
            const endRow = Math.min((table.getState().pagination.pageIndex + 1) * currentPageSize, totalRows);

            const maxVisibleItems = 7;
            const pages = [];

            if (totalPages <= maxVisibleItems) {
                for (let i = 1; i <= totalPages; i++) {
                    pages.push(i);
                }
            } else {
                pages.push(1);
                let remainingSlots = maxVisibleItems - 3;

                if (currentPage > 4) {
                    pages.push('...');
                    remainingSlots--;
                }

                const startPage = Math.max(2, currentPage - Math.floor(remainingSlots / 2));
                const endPage = Math.min(totalPages - 1, startPage + remainingSlots - 1);

                for (let i = startPage; i <= endPage; i++) {
                    pages.push(i);
                }

                if (endPage < totalPages - 1) {
                    pages.push('...');
                }

                pages.push(totalPages);
            }

            return html`
                <div class="fixed-table-pagination">
                    <div class="float-left pagination-detail">
                        <span class="pagination-info">
                            Showing ${startRow} to ${endRow} of ${totalRows} entries
                        </span>
                        ${renderPageSizeDropdown()}
                    </div>
                    <div class="float-right pagination">
                        <ul class="pagination">
                            <li
                                class="page-item page-pre ${!table.getCanPreviousPage() ? 'disabled' : ''}"
                                onclick="${table.getCanPreviousPage() ? 'table.previousPage()' : ''}"
                                style="user-select: none; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none;"
                            >
                                <a class="page-link" aria-label="previous page" style="user-select: none; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none;">‹</a>
                            </li>
                            ${pages.map(page => {
                                if (page === '...') {
                                    return html`
                                        <li class="page-item page-last-separator disabled" style="user-select: none; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none;">
                                            <a class="page-link" style="user-select: none; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none;">...</a>
                                        </li>
                                    `;
                                }
                                return html`
                                    <li
                                        class="page-item ${page === currentPage ? 'active' : ''}"
                                        onclick="table.setPageIndex(${page - 1})"
                                        style="user-select: none; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none;"
                                    >
                                        <a class="page-link" aria-label="to page ${page}" style="user-select: none; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none;">${page}</a>
                                    </li>
                                `;
                            })}
                            <li
                                class="page-item page-next ${!table.getCanNextPage() ? 'disabled' : ''}"
                                onclick="${table.getCanNextPage() ? 'table.nextPage()' : ''}"
                                style="user-select: none; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none;"
                            >
                                <a class="page-link" aria-label="next page" style="user-select: none; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none;">›</a>
                            </li>
                        </ul>
                    </div>
                </div>
            `;
        };

        const renderToolbar = () => {
            const visibleColumns = table.getAllColumns().filter(column =>
                column.id !== 'select' && !(isTrash && column.id === 'trash')
            );

            return html`
                <div class="fixed-table-toolbar">
                    <div class="float-left bs-bars">
                        <div id="${toolbarId}" class="btn-group">
                            ${hasMassiveAction && !radio ? html`
                                <button
                                    type="button"
                                    class="btn btn-secondary"
                                    aria-label="Actions"
                                    onclick="massiveaction_window${config.id}.dialog('open')"
                                >
                                    <i class="fas fa-hammer" title="Actions"></i>
                                </button>
                            ` : ''}
                            ${columnEdit ? html`
                                <button
                                    type="button"
                                    class="btn btn-secondary"
                                    aria-label="Options"
                                    onclick="$('#search-config-${itemtype}').dialog('open')"
                                >
                                    <i class="fas fa-wrench" title="Options"></i>
                                </button>
                            ` : ''}
                        </div>
                    </div>
                    <div class="float-right btn-group columns columns-right">
                        ${canTrash ? html`
                            <button
                                type="button"
                                class="btn btn-secondary${isTrash ? ' active btn-danger' : ''}"
                                onclick="toogle('is_deleted', '', '', ''); if (document.forms['searchform${itemtype}']) { document.forms['searchform${itemtype}'].submit(); }"
                                title="${isTrash ? 'Show normal items' : 'Show trash content'}"
                            >
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        ` : ''}
                        <div class="btn-group keep-open">
                            <button
                                type="button"
                                class="btn btn-secondary dropdown-toggle"
                                data-bs-toggle="dropdown"
                                aria-haspopup="true"
                            >
                                <i class="bi fas fa-columns"></i> <span class="caret"></span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <label class="dropdown-item dropdown-item-marker">
                                    <input
                                        type="checkbox"
                                        checked
                                        data-column="all"
                                        data-action="toggle-all-columns"
                                    />
                                    Toggle All
                                </label>
                                <div class="dropdown-divider"></div>
                                ${visibleColumns.map(column => html`
                                    <label class="dropdown-item dropdown-item-marker">
                                        <input
                                            type="checkbox"
                                            checked="${column.getIsVisible()}"
                                            onchange="column.toggleVisibility(event.target.checked)"
                                            data-column-id="${column.id}"
                                        />
                                        <span>${fields[column.id]}</span>
                                    </label>
                                `)}
                            </div>
                        </div>
                        ${showExport ? html`
                            <button type="button" class="btn btn-secondary" id="export-table" title="Export">
                                <i class="fas fa-file-export"></i>
                            </button>
                        ` : ''}
                    </div>
                </div>
            `;
        };

        const renderTableElement = () => {
            return html`
                <div class="fixed-table-container" style="overflow-x: scroll;">
                    <table class="table table-striped table-bordered table-hover">
                        <thead>
                            ${table.getHeaderGroups().map(headerGroup => html`
                                <tr>
                                    ${headerGroup.headers.map(header => {
                                        const isSelectColumn = header.id === 'select';
                                        const canSort = header.column.getCanSort();
                                        const sortingState = header.column.getIsSorted();

                                        let thClasses = '';
                                        let thInnerClasses = 'th-inner';

                                        if (isSelectColumn) {
                                            thClasses = 'bs-checkbox';
                                        } else if (canSort) {
                                            thInnerClasses += ' sortable both';
                                            if (sortingState === 'asc') {
                                                thInnerClasses += ' asc';
                                            } else if (sortingState === 'desc') {
                                                thInnerClasses += ' desc';
                                            }
                                        }

                                        return html`
                                            <th
                                                class="${thClasses}"
                                                data-field="${header.column.id}"
                                                style="${isSelectColumn ? 'width: 36px' : ''}"
                                            >
                                                <div class="${thInnerClasses}">
                                                    ${flexRender(header.column.columnDef.header, header.getContext())}
                                                </div>
                                                <div class="fht-cell"></div>
                                            </th>
                                        `;
                                    })}
                                </tr>
                            `)}
                        </thead>
                        ${table.getRowModel().rows.length === 0 ? html`
                            <tbody class="table-light">
                                <tr class="no-records-found">
                                    <td colspan="${table.getAllColumns().length}">
                                        No matching records found
                                    </td>
                                </tr>
                            </tbody>
                        ` : html`
                        <tbody class="table-light">
                            ${table.getRowModel().rows.map(row => html`
                                <tr>
                                    ${row.getVisibleCells().map(cell => {
                                        const isSelectColumn = cell.column.id === 'select';
                                        const isDisabled = isSelectColumn && !radio && hasMassiveAction && !row.original.value;

                                        return html`
                                            <td
                                                class="${isSelectColumn ? 'bs-checkbox' : ''}"
                                                style="${isSelectColumn ? 'width: 36px' : ''}"
                                            >
                                                ${isSelectColumn && isDisabled ?
                                                    html`<input type="checkbox" class="row-select" data-row-id="${row.id}" disabled />` :
                                                    flexRender(cell.column.columnDef.cell, cell.getContext())
                                                }
                                            </td>
                                        `;
                                    })}
                                </tr>
                            `)}
                        </tbody>
                        `}
                    </table>
                </div>
            `;
        };

        const exportToCsv = () => {
            const dataToExport = table.getFilteredRowModel().rows.map(row => row.original);
            if (dataToExport.length === 0) {
                return;
            }
            const headers = Object.keys(fields);
            const csvRows = [];
            csvRows.push(headers.map(h => fields[h]).join(','));

            for (const row of dataToExport) {
                const valuesToExport = headers.map(header => {
                    let value = row[header];
                    if (typeof value === 'string') {
                        const tempEl = document.createElement('div');
                        tempEl.innerHTML = value;
                        value = tempEl.textContent || tempEl.innerText || '';
                    }
                    return JSON.stringify(value);
                });
                csvRows.push(valuesToExport.join(','));
            }

            const csvContent = csvRows.join('\n');

            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            const exportUrl = URL.createObjectURL(blob);
            link.setAttribute('href', exportUrl);
            link.setAttribute('download', (itemtype || 'export') + '.csv');
            link.style.visibility = 'hidden';
            wrapperElement.appendChild(link);
            link.click();
            wrapperElement.removeChild(link);
        };

        const toggleAllColumns = (event) => {
            const isChecked = event.target.checked;
            table.getAllColumns().forEach((column) => {
                if (column.id === 'select' || (isTrash && column.id === 'trash')) {
                    return;
                }
                column.toggleVisibility(isChecked);
            });
        };

        const handleSelectAll = (event) => {
            if (!hasMassiveAction) {
                event.target.checked = false;
                return;
            }
            const isChecked = event.target.checked;
            const checkboxes = wrapperElement.querySelectorAll('.row-select');
            checkboxes.forEach((checkbox) => {
                if (!checkbox.disabled) {
                    checkbox.checked = isChecked;
                }
            });
            if (isChecked) {
                massiveActionSelection = table.getRowModel().rows
                    .filter(row => {
                        const checkbox = wrapperElement.querySelector(`.row-select[data-row-id="${row.id}"]`);
                        return checkbox && !checkbox.disabled;
                    })
                    .map(row => row.original);
            } else {
                massiveActionSelection = [];
            }
            setTimeout(() => {
                const selectAllCheckbox = wrapperElement.querySelector('#select-all');
                if (selectAllCheckbox) {
                    selectAllCheckbox.checked = isChecked;
                }
            }, 0);
        };

        const updateMassiveActionSelection = (rowId, isSelected) => {
            const row = table.getRowModel().rowsById[rowId];
            if (!row) return;
            const massiveActionId = row.original.value;

            if (isSelected) {
                if (!massiveActionSelection.some(item => item.value === massiveActionId)) {
                    massiveActionSelection.push(row.original);
                }
            } else {
                massiveActionSelection = massiveActionSelection.filter(item => item.value !== massiveActionId);
            }
            const checkboxes = wrapperElement.querySelectorAll('.row-select');
            const allChecked = Array.from(checkboxes).filter(cb => !cb.disabled).every(cb => cb.checked);
            const selectAllCheckbox = wrapperElement.querySelector('#select-all');
            if (selectAllCheckbox) {
                selectAllCheckbox.checked = allChecked;
            }
        };

        const handleRowSelection = (event) => {
            const checkbox = event.target;
            const rowId = checkbox.getAttribute('data-row-id');
            const isSelected = checkbox.checked;
            if (!radio) {
                updateMassiveActionSelection(rowId, isSelected);
            }
        };

        const initializeRowSelectionEvents = () => {
            wrapperElement.querySelectorAll('.row-select').forEach(checkbox => {
                checkbox.addEventListener('change', handleRowSelection);
            });
        };

        const renderTable = () => {
            htmlInjectionMap.clear();
            const totalRows = url ? serverTotal : data.length;
            const shouldPaginate = url
                ? (serverTotal !== null && serverTotal > table.getState().pagination.pageSize)
                : (data.length > table.getState().pagination.pageSize);
            const showPageSizeSelector = totalRows !== null && totalRows > 0;
            const tableHTML = html`
            <div>
                ${(!minimal || !noToolBar) ? renderToolbar() : ''}
                ${renderTableElement()}
                ${shouldPaginate ? renderPagination() : (showPageSizeSelector ? renderPageSizeSelector() : '')}
            </div>`;
            let finalHTML = tableHTML;
            htmlInjectionMap.forEach((htmlContent, marker) => { finalHTML = finalHTML.replace(marker, htmlContent); });
            wrapperElement.innerHTML = finalHTML;

            const selectAllCheckbox = wrapperElement.querySelector('#select-all');
            if (selectAllCheckbox) selectAllCheckbox.addEventListener('change', handleSelectAll);

            const exportButton = wrapperElement.querySelector('#export-table');
            if (exportButton) exportButton.addEventListener('click', exportToCsv);

            const toggleAllCheckbox = wrapperElement.querySelector('[data-action="toggle-all-columns"]');
            if (toggleAllCheckbox) toggleAllCheckbox.addEventListener('change', toggleAllColumns);

            initializeRowSelectionEvents();
            wrapperElement.querySelectorAll('[data-column-id]').forEach(checkbox => {
                const columnId = checkbox.getAttribute('data-column-id');
                const column = table.getAllColumns().find(col => col.id === columnId);
                if (column) {
                    checkbox.onchange = (event) => {
                        column.toggleVisibility(event.target.checked);
                    };
                }
            });
            wrapperElement.querySelectorAll('th[data-field] .sortable').forEach(element => {
                const fieldName = element.closest('th').getAttribute('data-field');
                const column = table.getAllColumns().find(col => col.id === fieldName);
                if (column && column.getCanSort()) {
                    element.onclick = () => {
                        column.toggleSorting();
                    };
                }
            });
            wrapperElement.querySelectorAll('.page-item:not(.disabled)').forEach(item => {
                if (item.classList.contains('page-pre')) {
                    item.onclick = () => {
                        if (table.getCanPreviousPage()) {
                            table.previousPage();
                        }
                    };
                } else if (item.classList.contains('page-next')) {
                    item.onclick = () => {
                        if (table.getCanNextPage()) {
                            table.nextPage();
                        }
                    };
                } else if (!item.classList.contains('page-last-separator')) {
                    const pageText = item.querySelector('.page-link').textContent;
                    if (!isNaN(pageText)) {
                        item.onclick = () => {
                            table.setPageIndex(parseInt(pageText, 10) - 1);
                        };
                    }
                }
            });
            wrapperElement.querySelectorAll('.dropdown-item').forEach(item => {
                const size = parseInt(item.textContent, 10);
                if ([15, 25, 50, 100, 1000, 10000].includes(size)) {
                    item.onclick = () => {
                        table.setPageSize(size);
                    };
                }
            });
        };

        if (url) {
            renderTable();
            fetchDataAndRender();
        } else {
            validatePaginationState(data.length);
            renderTable();
        }

        if (radio) {
            const form = wrapperElement.closest('form');
            if (form) {
                form.addEventListener('submit', () => {
                    const input = document.getElementById('table_input' + config.id);
                    if (input) {
                        const selectedRadio = wrapperElement.querySelector('.row-select:checked');
                        if (selectedRadio) {
                            const rowId = selectedRadio.getAttribute('data-row-id');
                            const selectedRow = data[rowId];
                            input.value = JSON.stringify([selectedRow]);
                        } else {
                            input.value = '[]';
                        }
                    }
                });
            }
        }
    }

    existingQueue.forEach(initTable);
    const originalPush = existingQueue.push.bind(existingQueue);
    existingQueue.push = function (config) {
        const length = originalPush(config);
        initTable(config);
        return length;
    };

    window.ITSMTableQueue = existingQueue;
    window.ITSMTable = window.ITSMTable || {};
    window.ITSMTable.init = initTable;
})(window, document);

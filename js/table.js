(function (window, document) {
    const existingQueue = Array.isArray(window.ITSMTableQueue) ? window.ITSMTableQueue : [];

    const preactGlobal = window.preact;
    const hooksGlobal = window.preactHooks;
    const htmGlobal = window.htm;

    if (!preactGlobal || !hooksGlobal || !htmGlobal) {
        return;
    }

    const { h, render } = preactGlobal;
    const { useEffect } = hooksGlobal;
    const html = htmGlobal.bind(h);

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

        if (!TableCore || !Nanostores) {
            console.error('Table dependencies missing.');
            return;
        }

        let massiveActionSelection = [];
        window[config.id + '_getMassiveActionSelection'] = function () {
            return massiveActionSelection;
        };

        const { createColumnHelper, createTable, getCoreRowModel, getPaginationRowModel, getSortedRowModel } = TableCore;
        const { atom } = Nanostores;
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
        const exportTarget = config.exportTarget || null;
        const exportParams = config.exportParams || null;

        if (!url && hasMassiveAction) {
            Object.keys(values).forEach(key => {
                if (values[key]) {
                    values[key].value = massiveAction[key];
                }
            });
        }

        let table;
        let serverTotal = null;
        let isLoading = false;
        let hasLoadingOverlay = false;

        const decodeHtml = (htmlString) => {
            const txt = document.createElement('textarea');
            txt.innerHTML = htmlString;
            return txt.value;
        };

        const processSpecialCharacters = (value) => {
            if (typeof value !== 'string') return value;
            return value
                .replace(/\$\$##\$\$/g, '<hr>')
                .replace(/\$#\$/g, ' ')
                .replace(/#LBBR#/g, '<br>')
                .replace(/#LBHR#/g, '<hr>');
        };

        const renderRawHTML = (value) => {
            const processed = processSpecialCharacters(value);
            if (typeof processed !== 'string') {
                return processed;
            }
            if (/<[^>]*>/.test(processed)) {
                return h('span', { dangerouslySetInnerHTML: { __html: processed } });
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

        let renderTable = () => {};

        const fetchDataAndRender = async () => {
            if (!url) return;
            isLoading = true;
            hasLoadingOverlay = true;
            renderTable();

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
                    isLoading = false;
                    renderTable();
                    fetchDataAndRender();
                    return;
                }
            } catch (error) {
                console.error('Failed to fetch table data:', error);
                table.setOptions(prev => ({ ...prev, data: [] }));
                serverTotal = 0;
            } finally {
                isLoading = false;
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
            header: () => renderRawHTML(fields[field]),
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

        const EXPORT_FORMATS = {
            CSV: 3,
            PDF_LANDSCAPE: 2,
            PDF_PORTRAIT: 4,
            SYLK: 1
        };

        const performExport = (format, exportAll = false) => {
            if (!exportTarget || !exportParams) {
                exportToCsv();
                return;
            }

            const displayType = exportAll ? -format : format;
            const params = new URLSearchParams();
            
            if (exportParams.item_type) {
                params.append('item_type', exportParams.item_type);
            }
            
            if (!exportAll) {
                const { pageIndex, pageSize } = table.getState().pagination;
                params.append('start', pageIndex * pageSize);
                params.append('list_limit', pageSize);
            } else {
                params.append('start', '0');
            }
            
            if (exportParams.criteria && Array.isArray(exportParams.criteria)) {
                exportParams.criteria.forEach((c, i) => {
                    Object.keys(c).forEach(key => {
                        params.append(`criteria[${i}][${key}]`, c[key]);
                    });
                });
            }
            
            if (exportParams.metacriteria && Array.isArray(exportParams.metacriteria)) {
                exportParams.metacriteria.forEach((c, i) => {
                    Object.keys(c).forEach(key => {
                        params.append(`metacriteria[${i}][${key}]`, c[key]);
                    });
                });
            }

            params.append('sort', exportParams.sort || '1');
            params.append('order', exportParams.order || 'ASC');
            if (exportParams.is_deleted) {
                params.append('is_deleted', '1');
            }
            params.append('display_type', displayType);
            if (exportAll) {
                params.append('export_all', '1');
            }

            window.open(exportTarget + '?' + params.toString(), '_blank');
        };

        const renderExportDropdown = () => {
            const hasServerExport = exportTarget && exportParams;
            
            return html`
                <div class="btn-group keep-open">
                    <button
                        type="button"
                        class="btn btn-secondary dropdown-toggle"
                        data-bs-toggle="dropdown"
                        aria-haspopup="true"
                        title="Export"
                    >
                        <i class="fas fa-file-export"></i> <span class="caret"></span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <h6 class="dropdown-header">${__("Current page")}</h6>
                        <a class="dropdown-item" onClick=${() => performExport(EXPORT_FORMATS.CSV)}>
                            <i class="fas fa-file-csv"></i> ${__("CSV")}
                        </a>
                        ${hasServerExport ? html`
                            <a class="dropdown-item" onClick=${() => performExport(EXPORT_FORMATS.PDF_LANDSCAPE)}>
                                <i class="fas fa-file-pdf"></i> ${__("PDF (Landscape)")}
                            </a>
                            <a class="dropdown-item" onClick=${() => performExport(EXPORT_FORMATS.PDF_PORTRAIT)}>
                                <i class="fas fa-file-pdf"></i> ${__("PDF (Portrait)")}
                            </a>
                            <a class="dropdown-item" onClick=${() => performExport(EXPORT_FORMATS.SYLK)}>
                                <i class="fas fa-file-excel"></i> ${__("SLK (Excel)")}
                            </a>
                            <div class="dropdown-divider"></div>
                            <h6 class="dropdown-header">${__("All pages")}</h6>
                            <a class="dropdown-item" onClick=${() => performExport(EXPORT_FORMATS.CSV, true)}>
                                <i class="fas fa-file-csv"></i> ${__("CSV")}
                            </a>
                            <a class="dropdown-item" onClick=${() => performExport(EXPORT_FORMATS.PDF_LANDSCAPE, true)}>
                                <i class="fas fa-file-pdf"></i> ${__("PDF (Landscape)")}
                            </a>
                            <a class="dropdown-item" onClick=${() => performExport(EXPORT_FORMATS.PDF_PORTRAIT, true)}>
                                <i class="fas fa-file-pdf"></i> ${__("PDF (Portrait)")}
                            </a>
                            <a class="dropdown-item" onClick=${() => performExport(EXPORT_FORMATS.SYLK, true)}>
                                <i class="fas fa-file-excel"></i> ${__("SLK (Excel)")}
                            </a>
                        ` : ''}
                    </div>
                </div>
            `;
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

        const openMassiveAction = () => {
            const dialog = window['massiveaction_window' + config.id];
            if (dialog && typeof dialog.dialog === 'function') {
                dialog.dialog('open');
            }
        };

        const toggleTrash = () => {
            if (typeof window.toogle === 'function') {
                window.toogle('is_deleted', '', '', '');
            }
            const form = document.forms['searchform' + itemtype];
            if (form) {
                form.submit();
            }
        };

        const openDisplayPreferences = (targetItemtype) => {
            if (window.DisplayPreferences && typeof window.DisplayPreferences.open === 'function') {
                window.DisplayPreferences.open(targetItemtype);
            }
        };

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
                                    onClick=${() => table.setPageSize(size)}
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
                                class=${'page-item page-pre ' + (!table.getCanPreviousPage() ? 'disabled' : '')}
                                onClick=${table.getCanPreviousPage() ? () => table.previousPage() : null}
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
                                        class=${'page-item ' + (page === currentPage ? 'active' : '')}
                                        onClick=${() => table.setPageIndex(page - 1)}
                                        style="user-select: none; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none;"
                                    >
                                        <a class="page-link" aria-label=${'to page ' + page} style="user-select: none; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none;">${page}</a>
                                    </li>
                                `;
                            })}
                            <li
                                class=${'page-item page-next ' + (!table.getCanNextPage() ? 'disabled' : '')}
                                onClick=${table.getCanNextPage() ? () => table.nextPage() : null}
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
                        <div id=${toolbarId} class="btn-group">
                            ${hasMassiveAction && !radio ? html`
                                <button
                                    type="button"
                                    class="btn btn-secondary"
                                    aria-label="Actions"
                                    onClick=${openMassiveAction}
                                >
                                    <i class="fas fa-hammer" title="Actions"></i>
                                </button>
                            ` : ''}
                            ${columnEdit ? html`
                                <button
                                    type="button"
                                    class="btn btn-secondary"
                                    aria-label="Options"
                                    data-display-preferences=${itemtype}
                                    onClick=${() => openDisplayPreferences(itemtype)}
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
                                class=${'btn btn-secondary' + (isTrash ? ' active btn-danger' : '')}
                                onClick=${toggleTrash}
                                title=${isTrash ? 'Show normal items' : 'Show trash content'}
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
                                        defaultChecked
                                        data-column="all"
                                        data-action="toggle-all-columns"
                                        onChange=${toggleAllColumns}
                                    />
                                    Toggle All
                                </label>
                                <div class="dropdown-divider"></div>
                                ${visibleColumns.map(column => html`
                                    <label class="dropdown-item dropdown-item-marker">
                                        <input
                                            type="checkbox"
                                            checked=${column.getIsVisible()}
                                            onChange=${(event) => column.toggleVisibility(event.target.checked)}
                                            data-column-id=${column.id}
                                        />
                                        <span>${fields[column.id]}</span>
                                    </label>
                                `)}
                            </div>
                        </div>
                        ${showExport ? renderExportDropdown() : ''}
                    </div>
                </div>
            `;
        };

        const renderTableElement = () => {
            const tableContainerStyle = 'overflow-x: scroll;' + (hasLoadingOverlay ? ' position: relative;' : '');

            return html`
                <div class="fixed-table-container" style=${tableContainerStyle}>
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
                                                class=${thClasses}
                                                data-field=${header.column.id}
                                                style=${isSelectColumn ? 'width: 36px' : ''}
                                            >
                                                <div
                                                    class=${thInnerClasses}
                                                    onClick=${canSort ? () => header.column.toggleSorting() : null}
                                                >
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
                                    <td colspan=${table.getAllColumns().length}>
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
                                                class=${isSelectColumn ? 'bs-checkbox' : ''}
                                                style=${isSelectColumn ? 'width: 36px' : ''}
                                            >
                                                ${isSelectColumn && isDisabled ?
                                                    html`<input type="checkbox" class="row-select" data-row-id=${row.id} disabled />` :
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
                    ${isLoading ? html`
                        <div
                            class="loading-overlay"
                            style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255, 255, 255, 0.8); display: flex; align-items: center; justify-content: center; z-index: 10;"
                        >
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    ` : ''}
                </div>
            `;
        };

        const TableApp = () => {
            useEffect(() => {
                if (!radio) {
                    return undefined;
                }
                const form = wrapperElement.closest('form');
                if (!form) {
                    return undefined;
                }
                const handler = () => {
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
                };
                form.addEventListener('submit', handler);
                return () => {
                    form.removeEventListener('submit', handler);
                };
            }, []);

            const totalRows = url ? serverTotal : data.length;
            const shouldPaginate = url
                ? (serverTotal !== null && serverTotal > table.getState().pagination.pageSize)
                : (data.length > table.getState().pagination.pageSize);
            const showPageSizeSelector = totalRows !== null && totalRows > 0;

            return html`
                <div>
                    ${(!minimal || !noToolBar) ? renderToolbar() : ''}
                    ${renderTableElement()}
                    ${shouldPaginate ? renderPagination() : (showPageSizeSelector ? renderPageSizeSelector() : '')}
                </div>
            `;
        };

        renderTable = () => {
            render(null, wrapperElement);
            render(html`<${TableApp} />`, wrapperElement);

            const selectAllCheckbox = wrapperElement.querySelector('#select-all');
            if (selectAllCheckbox) selectAllCheckbox.addEventListener('change', handleSelectAll);

            wrapperElement.querySelectorAll('.row-select').forEach(checkbox => {
                checkbox.addEventListener('change', handleRowSelection);
            });
        };

        if (url) {
            renderTable();
            fetchDataAndRender();
        } else {
            validatePaginationState(data.length);
            renderTable();
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

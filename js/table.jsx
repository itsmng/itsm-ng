import {
    createColumnHelper,
    createTable,
    getCoreRowModel,
    getFilteredRowModel,
    getPaginationRowModel,
    getSortedRowModel,
} from '@tanstack/table-core';
import { Fragment, h, render } from 'preact';
import { useEffect } from 'preact/hooks';
import { batch, signal } from '@preact/signals';

(function (window, document) {
    const instances = new Map();

    const normalizeTableConfig = (rawConfig) => {
        if (!rawConfig || typeof rawConfig !== 'object') {
            console.error('ITSMTable: missing table configuration');
            return null;
        }

        if (!rawConfig.id) {
            console.error('ITSMTable: table configuration requires an id', rawConfig);
            return null;
        }

        const columns = Array.isArray(rawConfig.columns) ? rawConfig.columns : [];
        if (!columns.length) {
            console.error('ITSMTable: table configuration requires at least one column', rawConfig);
            return null;
        }

        const dataSource = rawConfig.dataSource || {};
        const isRemote = dataSource.type === 'remote';
        if (isRemote && !dataSource.url) {
            console.error('ITSMTable: remote data source requires an url', rawConfig);
            return null;
        }

        const selection = rawConfig.selection || {};
        const toolbar = rawConfig.toolbar || {};
        const trash = toolbar.trash || {};
        const view = rawConfig.view || {};
        const exportConfig = rawConfig.export || {};
        const search = rawConfig.search || {};

        return {
            id: String(rawConfig.id),
            stateKey: String(rawConfig.stateKey || rawConfig.itemtype || rawConfig.id),
            itemtype: rawConfig.itemtype || '',
            columns,
            dataSource: {
                type: isRemote ? 'remote' : 'local',
                url: isRemote ? dataSource.url : null,
                rows: isRemote ? [] : (dataSource.rows || []),
            },
            selection: {
                type: selection.type || 'none',
                values: selection.values || [],
                inputId: selection.inputId || null,
            },
            toolbar: {
                enabled: toolbar.enabled !== false,
                columns: toolbar.columns !== false,
                massiveAction: toolbar.massiveAction !== false,
                displayPreferences: !!toolbar.displayPreferences,
                id: toolbar.id || 'toolbar' + Math.floor(Math.random() * 1000000),
                trash: {
                    enabled: !!trash.enabled,
                    isTrash: !!trash.isTrash,
                },
            },
            view: {
                pageSize: view.pageSize || 15,
            },
            export: {
                enabled: exportConfig.enabled !== false,
                target: exportConfig.target || null,
                params: exportConfig.params || null,
            },
            search: {
                enabled: !!search.enabled,
                placeholder: search.placeholder || 'Search',
            },
        };
    };

    function initTable(rawConfig) {
        const config = normalizeTableConfig(rawConfig);
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

        const fieldsArray = config.columns.map(column => [String(column.id), column.label]);
        const fields = Object.fromEntries(fieldsArray);
        const fieldKeys = fieldsArray.map(pair => pair[0]);

        const values = config.dataSource.rows || [];
        const massiveAction = config.selection.values || [];
        const radio = config.selection.type === 'radio';
        const hasMassiveAction = config.selection.type === 'massive-action';
        const columnEdit = !!config.toolbar.displayPreferences;
        const toolbarEnabled = !!config.toolbar.enabled;
        const isTrash = !!config.toolbar.trash.isTrash;
        const canTrash = !!config.toolbar.trash.enabled;
        const url = config.dataSource.type === 'remote' ? config.dataSource.url : null;
        const itemtype = config.itemtype || '';
        const stateKey = config.stateKey;
        const showExport = config.export.enabled !== false;
        const pageSize = config.view.pageSize || 15;
        const toolbarId = config.toolbar.id;
        const exportTarget = config.export.target || null;
        const exportParams = config.export.params || null;
        const hasServerExport = !!(exportTarget && exportParams);
        const canExport = showExport && hasServerExport;
        const searchEnabled = !!(config.search && config.search.enabled);
        const tableStateSignal = signal({});
        const tableDataSignal = signal([]);
        const serverTotalSignal = signal(null);
        const isLoadingSignal = signal(false);
        const hasLoadingOverlaySignal = signal(false);
        const massiveActionSelectionSignal = signal([]);

        if (!url && hasMassiveAction) {
            Object.keys(values).forEach(key => {
                if (values[key]) {
                    values[key].value = massiveAction[key];
                }
            });
        }

        let table;
        let fetchRequestId = 0;

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
            localStorage.setItem('tableState_pagination_' + stateKey, JSON.stringify(state.pagination));
            localStorage.setItem('tableState_sorting_' + stateKey, JSON.stringify(state.sorting));
            localStorage.setItem('tableState_visibility_' + stateKey, JSON.stringify(state.columnVisibility));
        };

        const loadState = () => {
            const pagination = JSON.parse(localStorage.getItem('tableState_pagination_' + stateKey) || 'null');
            const sorting = JSON.parse(localStorage.getItem('tableState_sorting_' + stateKey) || 'null');
            const visibility = JSON.parse(localStorage.getItem('tableState_visibility_' + stateKey) || 'null');
            return { pagination, sorting, visibility };
        };

        const getPageCount = (totalRows, currentPageSize) => Math.ceil(totalRows / currentPageSize);

        const getValidPaginationState = (state, totalRows) => {
            const { pageIndex, pageSize: currentPageSize } = state.pagination;
            const maxPageIndex = Math.max(0, Math.ceil(totalRows / currentPageSize) - 1);

            if (pageIndex > maxPageIndex) {
                return {
                    ...state,
                    pagination: {
                        ...state.pagination,
                        pageIndex: 0,
                    },
                };
            }
            return state;
        };

        const hasTableStateChanged = (oldState, newState, key) => {
            return JSON.stringify(oldState[key]) !== JSON.stringify(newState[key]);
        };

        const fetchData = async (state = tableStateSignal.value) => {
            if (!url) return;

            const requestId = ++fetchRequestId;
            batch(() => {
                isLoadingSignal.value = true;
                hasLoadingOverlaySignal.value = true;
            });

            const { pageIndex, pageSize: currentPageSize } = state.pagination;
            const { sorting } = state;

            const fetchUrl = new URL(url.replace(/&amp;/g, '&'), window.location.origin);
            fetchUrl.searchParams.set('offset', pageIndex * currentPageSize);
            fetchUrl.searchParams.set('limit', currentPageSize);

            if (sorting && sorting.length > 0) {
                fetchUrl.searchParams.set('sort', sorting[0].id);
                fetchUrl.searchParams.set('order', sorting[0].desc ? 'desc' : 'asc');
            }
            if (state.globalFilter) {
                fetchUrl.searchParams.set('search', state.globalFilter);
            }

            try {
                const response = await fetch(fetchUrl.toString());
                const result = await response.json();

                if (requestId !== fetchRequestId) {
                    return;
                }

                batch(() => {
                    serverTotalSignal.value = result.total;
                    tableDataSignal.value = result.rows;
                });

                const validState = getValidPaginationState(state, result.total);
                if (validState !== state) {
                    tableStateSignal.value = validState;
                    storeState(validState);
                    await fetchData(validState);
                    return;
                }
            } catch (error) {
                if (requestId !== fetchRequestId) {
                    return;
                }
                console.error('Failed to fetch table data:', error);
                batch(() => {
                    tableDataSignal.value = [];
                    serverTotalSignal.value = 0;
                });
            } finally {
                if (requestId === fetchRequestId) {
                    isLoadingSignal.value = false;
                }
            }
        };

        const updateTableState = (updater) => {
            const currentState = tableStateSignal.value;
            let newState = typeof updater === 'function' ? updater(currentState) : updater;
            const paginationChanged = hasTableStateChanged(currentState, newState, 'pagination');
            const sortingChanged = hasTableStateChanged(currentState, newState, 'sorting');
            const searchChanged = hasTableStateChanged(currentState, newState, 'globalFilter');

            if (searchChanged) {
                newState = {
                    ...newState,
                    pagination: {
                        ...newState.pagination,
                        pageIndex: 0,
                    },
                };
            }

            if (url && (paginationChanged || searchChanged)) {
                massiveActionSelectionSignal.value = [];
            }

            tableStateSignal.value = newState;
            storeState(newState);

            if (url && (paginationChanged || sortingChanged || searchChanged)) {
                fetchData(newState);
            }
        };

        const createSignalTable = (options) => {
            const { data: initialData, ...restOptions } = options;
            const { pagination, sorting, visibility } = loadState();
            const initialState = {
                ...restOptions.initialState,
                pagination: pagination || restOptions.initialState.pagination,
                ...(sorting ? { sorting } : {}),
                ...(visibility ? { columnVisibility: visibility } : {}),
            };
            const tableInstance = createTable({
                state: {},
                onStateChange: updateTableState,
                getCoreRowModel: getCoreRowModel(),
                getFilteredRowModel: getFilteredRowModel(),
                getPaginationRowModel: getPaginationRowModel(),
                getSortedRowModel: getSortedRowModel(),
                data: initialData,
                ...restOptions,
                initialState,
            });
            tableStateSignal.value = tableInstance.initialState;
            return tableInstance;
        };

        const columnHelper = createColumnHelper();
        let columns = [];
        if (radio) {
            columns.push(columnHelper.display({
                id: 'select',
                cell: info => (
                    <input
                        type="radio"
                        name={`row-select-${config.id}`}
                        class="row-select"
                        data-row-id={info.row.id}
                    />
                ),
            }));
        } else if (hasMassiveAction) {
            columns.push(columnHelper.display({
                id: 'select',
                header: () => (
                    <input
                        type="checkbox"
                        data-select-all-table={config.id}
                        checked={areAllVisibleRowsSelected()}
                        onChange={handleSelectAll}
                    />
                ),
                cell: info => (
                    <input
                        type="checkbox"
                        class="row-select"
                        data-row-id={info.row.id}
                        checked={isRowSelected(info.row)}
                        onChange={handleRowSelection}
                    />
                ),
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

        tableDataSignal.value = data;

        table = createSignalTable({
            data,
            columns,
            manualPagination: !!url,
            manualSorting: !!url,
            manualFiltering: !!url,
            initialState: {
                pagination: {
                    pageSize,
                },
                globalFilter: '',
            },
        });

        const EXPORT_FORMATS = {
            CSV: 3,
            PDF_LANDSCAPE: 2,
            PDF_PORTRAIT: 4,
            SYLK: 1
        };
        const EXPORT_OPTIONS = [
            { format: EXPORT_FORMATS.CSV, icon: 'fas fa-file-csv', label: 'CSV' },
            { format: EXPORT_FORMATS.PDF_LANDSCAPE, icon: 'fas fa-file-pdf', label: 'PDF (Landscape)' },
            { format: EXPORT_FORMATS.PDF_PORTRAIT, icon: 'fas fa-file-pdf', label: 'PDF (Portrait)' },
            { format: EXPORT_FORMATS.SYLK, icon: 'fas fa-file-excel', label: 'SLK (Excel)' },
        ];

        const performExport = (format, exportAll = false) => {
            if (!hasServerExport) {
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

        const renderExportItems = (exportAll = false) => EXPORT_OPTIONS.map(({ format, icon, label }) => (
            <a class="dropdown-item" onClick={() => performExport(format, exportAll)}>
                <i class={icon}></i> {__(label)}
            </a>
        ));

        const renderExportDropdown = () => (
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
                        <h6 class="dropdown-header">{__("Current page")}</h6>
                        {renderExportItems()}
                        <div class="dropdown-divider"></div>
                        <h6 class="dropdown-header">{__("All pages")}</h6>
                        {renderExportItems(true)}
                    </div>
                </div>
        );

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
            if (isChecked) {
                massiveActionSelectionSignal.value = table.getRowModel().rows
                    .filter(row => row.original.value)
                    .map(row => row.original);
            } else {
                massiveActionSelectionSignal.value = [];
            }
        };

        const updateMassiveActionSelection = (rowId, isSelected) => {
            const row = table.getRowModel().rowsById[rowId];
            if (!row) return;
            const massiveActionId = row.original.value;
            const currentSelection = massiveActionSelectionSignal.value;

            if (isSelected) {
                if (!currentSelection.some(item => item.value === massiveActionId)) {
                    massiveActionSelectionSignal.value = currentSelection.concat(row.original);
                }
            } else {
                massiveActionSelectionSignal.value = currentSelection.filter(item => item.value !== massiveActionId);
            }
        };

        const isRowSelected = (row) => {
            return massiveActionSelectionSignal.value.some(item => item.value === row.original.value);
        };

        const areAllVisibleRowsSelected = () => {
            const selectableRows = table.getRowModel().rows.filter(row => !!row.original.value);
            return selectableRows.length > 0 && selectableRows.every(isRowSelected);
        };

        const handleRowSelection = (event) => {
            const checkbox = event.target;
            const rowId = checkbox.getAttribute('data-row-id');
            const isSelected = checkbox.checked;
            if (!radio) {
                updateMassiveActionSelection(rowId, isSelected);
            }
        };

        let destroyStickyHeader = () => {};

        const initStickyHeader = () => {
            destroyStickyHeader();

            const pageElement = document.getElementById('page');
            const tableShell = wrapperElement.querySelector('.itsm-table-shell');
            const tableContainer = wrapperElement.querySelector('.fixed-table-container');
            const sourceTable = tableContainer ? tableContainer.querySelector('table') : null;
            const sourceThead = sourceTable ? sourceTable.querySelector('thead') : null;
            const stickyContainer = wrapperElement.querySelector('.sticky-table-header');

            if (!pageElement || !tableShell || !tableContainer || !sourceTable || !sourceThead || !stickyContainer) {
                destroyStickyHeader = () => {};
                return;
            }

            const stickyViewport = document.createElement('div');
            stickyViewport.className = 'sticky-table-header__viewport fixed-table-container';

            const pagePaddingTop = parseFloat(window.getComputedStyle(pageElement).paddingTop) || 0;
            stickyContainer.style.top = `${(-pagePaddingTop) - 1}px`;

            const stickyTable = sourceTable.cloneNode(false);
            stickyTable.setAttribute('aria-hidden', 'true');

            const clonedThead = sourceThead.cloneNode(true);
            const stickySelectAll = clonedThead.querySelector('[data-select-all-table]');
            if (stickySelectAll) {
                stickySelectAll.setAttribute('data-sticky-select-all', 'true');
            }

            stickyTable.appendChild(clonedThead);
            stickyViewport.appendChild(stickyTable);
            stickyContainer.replaceChildren(stickyViewport);

            const getSourceHeaders = () => Array.from(sourceThead.querySelectorAll('th'));
            const getStickyHeaders = () => Array.from(clonedThead.querySelectorAll('th'));

            const syncSelectAllState = () => {
                const sourceSelectAll = sourceThead.querySelector('[data-select-all-table]');
                const clonedSelectAll = clonedThead.querySelector('[data-sticky-select-all="true"]');
                if (sourceSelectAll && clonedSelectAll) {
                    clonedSelectAll.checked = sourceSelectAll.checked;
                    clonedSelectAll.indeterminate = sourceSelectAll.indeterminate;
                }
            };

            const syncHorizontalScroll = () => {
                stickyTable.style.transform = `translateX(${-tableContainer.scrollLeft}px)`;
            };

            const syncLayout = () => {
                const sourceHeaders = getSourceHeaders();
                const stickyHeaders = getStickyHeaders();

                sourceHeaders.forEach((sourceHeader, index) => {
                    const stickyHeader = stickyHeaders[index];
                    if (!stickyHeader) {
                        return;
                    }

                    const { width } = sourceHeader.getBoundingClientRect();
                    const computedStyle = window.getComputedStyle(sourceHeader);

                    stickyHeader.style.width = `${width}px`;
                    stickyHeader.style.minWidth = `${width}px`;
                    stickyHeader.style.maxWidth = `${width}px`;
                    stickyHeader.style.height = `${sourceHeader.getBoundingClientRect().height}px`;
                    stickyHeader.style.backgroundColor = computedStyle.backgroundColor;
                    stickyHeader.style.color = computedStyle.color;
                });

                stickyTable.style.width = `${sourceTable.getBoundingClientRect().width}px`;
                stickyViewport.style.width = `${tableContainer.clientWidth}px`;

                syncHorizontalScroll();
                syncSelectAllState();
            };

            const syncVisibility = () => {
                const pageRect = pageElement.getBoundingClientRect();
                const shellRect = tableShell.getBoundingClientRect();
                const headerHeight = sourceThead.getBoundingClientRect().height;
                const stickyActivationTop = pageRect.top + pagePaddingTop;
                const isStickyVisible = shellRect.top < stickyActivationTop && (shellRect.bottom - headerHeight) > stickyActivationTop;

                stickyContainer.classList.toggle('is-active', isStickyVisible);
            };

            const handleStickyClick = (event) => {
                const stickyCheckbox = event.target.closest('[data-sticky-select-all="true"]');
                if (stickyCheckbox) {
                    const sourceSelectAll = sourceThead.querySelector('[data-select-all-table]');
                    if (!sourceSelectAll) {
                        return;
                    }

                    event.preventDefault();
                    event.stopPropagation();

                    sourceSelectAll.checked = stickyCheckbox.checked;
                    sourceSelectAll.dispatchEvent(new Event('change', { bubbles: true }));
                    syncSelectAllState();
                    return;
                }

                const stickyInner = event.target.closest('.th-inner');
                if (!stickyInner) {
                    return;
                }

                const stickyHeader = stickyInner.closest('th');
                if (!stickyHeader) {
                    return;
                }

                const field = stickyHeader.getAttribute('data-field');
                const sourceHeader = getSourceHeaders().find((headerCell) => headerCell.getAttribute('data-field') === field);
                const sourceInner = sourceHeader ? sourceHeader.querySelector('.th-inner') : null;

                if (!sourceInner) {
                    return;
                }

                event.preventDefault();
                event.stopPropagation();
                sourceInner.click();
            };

            const handleWrapperChange = (event) => {
                if (event.target.closest('.sticky-table-header')) {
                    return;
                }

                syncSelectAllState();
            };

            const handlePageScroll = () => {
                syncVisibility();
            };

            const handleResize = () => {
                syncLayout();
                syncVisibility();
            };

            stickyContainer.addEventListener('click', handleStickyClick);
            tableContainer.addEventListener('scroll', syncHorizontalScroll);
            pageElement.addEventListener('scroll', handlePageScroll);
            wrapperElement.addEventListener('change', handleWrapperChange);
            window.addEventListener('resize', handleResize);

            let resizeObserver = null;
            if (window.ResizeObserver) {
                resizeObserver = new window.ResizeObserver(() => {
                    syncLayout();
                    syncVisibility();
                });
                resizeObserver.observe(sourceTable);
                resizeObserver.observe(tableContainer);
            }

            requestAnimationFrame(() => {
                syncLayout();
                syncVisibility();
            });

            destroyStickyHeader = () => {
                stickyContainer.removeEventListener('click', handleStickyClick);
                tableContainer.removeEventListener('scroll', syncHorizontalScroll);
                pageElement.removeEventListener('scroll', handlePageScroll);
                wrapperElement.removeEventListener('change', handleWrapperChange);
                window.removeEventListener('resize', handleResize);
                if (resizeObserver) {
                    resizeObserver.disconnect();
                }
                stickyContainer.replaceChildren();
            };
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

            return (
                <div class="page-list">
                    <div class="dropdown dropup">
                        <button
                            class="btn btn-secondary dropdown-toggle"
                            type="button"
                            data-bs-toggle="dropdown"
                        >
                            {currentPageSize}
                        </button>
                        <div class="dropdown-menu">
                            {pageSizeOptions.map(size => (
                                <a
                                    class="dropdown-item"
                                    onClick={() => table.setPageSize(size)}
                                >
                                    {size}
                                </a>
                            ))}
                        </div>
                    </div>
                </div>
            );
        };

        const renderPageSizeSelector = () => {
            const currentPageSize = table.getState().pagination.pageSize;
            const totalRows = url ? serverTotalSignal.value : table.getFilteredRowModel().rows.length;

            return (
                <div class="fixed-table-pagination">
                    <div class="float-left pagination-detail">
                        <span class="pagination-info">
                            Showing {Math.min(totalRows, currentPageSize)} of {totalRows} entries
                        </span>
                        {renderPageSizeDropdown()}
                    </div>
                </div>
            );
        };

        const renderPagination = () => {
            const totalPages = table.getPageCount();
            const currentPage = table.getState().pagination.pageIndex + 1;
            const currentPageSize = table.getState().pagination.pageSize;
            const totalRows = url ? serverTotalSignal.value : table.getFilteredRowModel().rows.length;
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

            return (
                <div class="fixed-table-pagination">
                    <div class="float-left pagination-detail">
                        <span class="pagination-info">
                            Showing {startRow} to {endRow} of {totalRows} entries
                        </span>
                        {renderPageSizeDropdown()}
                    </div>
                    <div class="float-right pagination">
                        <ul class="pagination">
                            <li
                                class={'page-item page-pre ' + (!table.getCanPreviousPage() ? 'disabled' : '')}
                                onClick={table.getCanPreviousPage() ? () => table.previousPage() : null}
                                style="user-select: none; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none;"
                            >
                                <a class="page-link" aria-label="previous page" style="user-select: none; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none;">‹</a>
                            </li>
                            {pages.map(page => {
                                if (page === '...') {
                                    return (
                                        <li class="page-item page-last-separator disabled" style="user-select: none; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none;">
                                            <a class="page-link" style="user-select: none; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none;">...</a>
                                        </li>
                                    );
                                }
                                return (
                                    <li
                                        class={'page-item ' + (page === currentPage ? 'active' : '')}
                                        onClick={() => table.setPageIndex(page - 1)}
                                        style="user-select: none; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none;"
                                    >
                                        <a class="page-link" aria-label={'to page ' + page} style="user-select: none; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none;">{page}</a>
                                    </li>
                                );
                            })}
                            <li
                                class={'page-item page-next ' + (!table.getCanNextPage() ? 'disabled' : '')}
                                onClick={table.getCanNextPage() ? () => table.nextPage() : null}
                                style="user-select: none; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none;"
                            >
                                <a class="page-link" aria-label="next page" style="user-select: none; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none;">›</a>
                            </li>
                        </ul>
                    </div>
                </div>
            );
        };

        const renderToolbar = () => {
            const visibleColumns = table.getAllColumns().filter(column =>
                column.id !== 'select' && !(isTrash && column.id === 'trash')
            );

            return (
                <div class="fixed-table-toolbar">
                    <div class="float-left bs-bars">
                        <div id={toolbarId} class="btn-group">
                            {hasMassiveAction && config.toolbar.massiveAction && !radio && (
                                <button
                                    type="button"
                                    class="btn btn-secondary"
                                    aria-label="Actions"
                                    onClick={openMassiveAction}
                                >
                                    <i class="fas fa-hammer" title="Actions"></i>
                                </button>
                            )}
                            {columnEdit && (
                                <button
                                    type="button"
                                    class="btn btn-secondary"
                                    aria-label="Options"
                                    data-display-preferences={itemtype}
                                    onClick={() => openDisplayPreferences(itemtype)}
                                >
                                    <i class="fas fa-wrench" title="Options"></i>
                                </button>
                            )}
                        </div>
                    </div>
                    {searchEnabled && (
                        <div class="float-left search">
                            <input
                                type="search"
                                class="form-control search-input"
                                value={table.getState().globalFilter || ''}
                                placeholder={__(config.search.placeholder)}
                                aria-label={__(config.search.placeholder)}
                                onInput={(event) => table.setGlobalFilter(event.target.value)}
                            />
                        </div>
                    )}
                    <div class="float-right btn-group columns columns-right">
                        {canTrash && (
                            <button
                                type="button"
                                class={'btn btn-secondary' + (isTrash ? ' active btn-danger' : '')}
                                onClick={toggleTrash}
                                title={isTrash ? 'Show normal items' : 'Show trash content'}
                            >
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        )}
                        {config.toolbar.columns && (
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
                                            onChange={toggleAllColumns}
                                        />
                                        Toggle All
                                    </label>
                                    <div class="dropdown-divider"></div>
                                    {visibleColumns.map(column => (
                                        <label class="dropdown-item dropdown-item-marker">
                                            <input
                                                type="checkbox"
                                                checked={column.getIsVisible()}
                                                onChange={(event) => column.toggleVisibility(event.target.checked)}
                                                data-column-id={column.id}
                                            />
                                            <span>{fields[column.id]}</span>
                                        </label>
                                    ))}
                                </div>
                            </div>
                        )}
                        {canExport && renderExportDropdown()}
                    </div>
                </div>
            );
        };

        const renderTableElement = () => {
            const tableContainerStyle = 'overflow-x: scroll;' + (hasLoadingOverlaySignal.value ? ' position: relative;' : '');

            return (
                <div class="itsm-table-shell">
                    <div class="sticky-table-header" aria-hidden="true"></div>
                    <div class="fixed-table-container" style={tableContainerStyle}>
                        <table class="table table-striped table-bordered table-hover">
                            <thead>
                                {table.getHeaderGroups().map(headerGroup => (
                                    <tr>
                                        {headerGroup.headers.map(header => {
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

                                            return (
                                                <th
                                                    class={thClasses}
                                                    data-field={header.column.id}
                                                    style={isSelectColumn ? 'width: 36px' : ''}
                                                >
                                                    <div
                                                        class={thInnerClasses}
                                                        onClick={canSort ? () => header.column.toggleSorting() : null}
                                                    >
                                                        {flexRender(header.column.columnDef.header, header.getContext())}
                                                    </div>
                                                    <div class="fht-cell"></div>
                                                </th>
                                            );
                                        })}
                                    </tr>
                                ))}
                            </thead>
                            {table.getRowModel().rows.length === 0 ? (
                                <tbody class="table-light">
                                    <tr class="no-records-found">
                                        <td colspan={table.getAllColumns().length}>
                                            No matching records found
                                        </td>
                                    </tr>
                                </tbody>
                            ) : (
                            <tbody class="table-light">
                                {table.getRowModel().rows.map(row => (
                                    <tr>
                                        {row.getVisibleCells().map(cell => {
                                            const isSelectColumn = cell.column.id === 'select';
                                            const isDisabled = isSelectColumn && !radio && hasMassiveAction && !row.original.value;

                                            return (
                                                <td
                                                    class={isSelectColumn ? 'bs-checkbox' : ''}
                                                    style={isSelectColumn ? 'width: 36px' : ''}
                                                >
                                                    {isSelectColumn && isDisabled ?
                                                        <input type="checkbox" class="row-select" data-row-id={row.id} disabled /> :
                                                        flexRender(cell.column.columnDef.cell, cell.getContext())
                                                    }
                                                </td>
                                            );
                                        })}
                                    </tr>
                                ))}
                            </tbody>
                            )}
                        </table>
                        {isLoadingSignal.value && (
                            <div
                                class="loading-overlay"
                                style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255, 255, 255, 0.8); display: flex; align-items: center; justify-content: center; z-index: 10;"
                            >
                                <div class="spinner-border" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        )}
                    </div>
                </div>
            );
        };

        const TableApp = () => {
            const tableState = tableStateSignal.value;
            const tableData = tableDataSignal.value;
            const serverTotal = serverTotalSignal.value;

            table.setOptions(prev => ({
                ...prev,
                data: tableData,
                state: tableState,
                onStateChange: updateTableState,
                pageCount: url && serverTotal !== null
                    ? getPageCount(serverTotal, tableState.pagination.pageSize)
                    : undefined,
            }));

            useEffect(() => {
                if (!radio) {
                    return undefined;
                }
                const form = wrapperElement.closest('form');
                if (!form) {
                    return undefined;
                }
                const handler = () => {
                    const input = document.getElementById(config.selection.inputId || ('table_input' + config.id));
                    if (input) {
                        const selectedRadio = wrapperElement.querySelector('.row-select:checked');
                        if (selectedRadio) {
                            const rowId = selectedRadio.getAttribute('data-row-id');
                            const selectedRow = table.getRowModel().rowsById[rowId];
                            input.value = JSON.stringify(selectedRow ? [selectedRow.original] : []);
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

            useEffect(() => {
                initStickyHeader();
                return () => {
                    destroyStickyHeader();
                };
            });

            const totalRows = url ? serverTotal : data.length;
            const shouldPaginate = url
                ? (serverTotal !== null && serverTotal > table.getState().pagination.pageSize)
                : (data.length > table.getState().pagination.pageSize);
            const showPageSizeSelector = totalRows !== null && totalRows > 0;

            return (
                <div>
                    {toolbarEnabled && renderToolbar()}
                    {renderTableElement()}
                    {shouldPaginate ? renderPagination() : (showPageSizeSelector && renderPageSizeSelector())}
                </div>
            );
        };

        instances.set(config.id, {
            id: config.id,
            refresh: () => fetchData(),
            getSelection: () => massiveActionSelectionSignal.value,
            getTable: () => table,
        });

        if (url) {
            tableDataSignal.value = [];
            render(<TableApp />, wrapperElement);
            fetchData();
        } else {
            const validState = getValidPaginationState(tableStateSignal.value, data.length);
            if (validState !== tableStateSignal.value) {
                tableStateSignal.value = validState;
                storeState(validState);
            }
            render(<TableApp />, wrapperElement);
        }
    }

    const readElementConfig = (wrapperElement) => {
        const configId = wrapperElement.dataset.itsmTableConfig;
        if (!configId) {
            return null;
        }

        const configElement = document.getElementById(configId);
        if (!configElement) {
            console.error('ITSMTable: missing JSON config element', configId);
            return null;
        }

        try {
            return JSON.parse(configElement.textContent || '{}');
        } catch (error) {
            console.error('ITSMTable: invalid JSON config', configId, error);
            return null;
        }
    };

    const initConfiguredTables = (root = document) => {
        if (root.matches && root.matches('[data-itsm-table-config]')) {
            initTable(readElementConfig(root));
        }

        root.querySelectorAll('[data-itsm-table-config]').forEach((wrapperElement) => {
            initTable(readElementConfig(wrapperElement));
        });
    };

    window.ITSMTable = window.ITSMTable || {};
    window.ITSMTable.init = initTable;
    window.ITSMTable.initAll = initConfiguredTables;
    window.ITSMTable.get = (id) => instances.get(String(id)) || null;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => initConfiguredTables(), { once: true });
    } else {
        initConfiguredTables();
    }

    if (window.jQuery) {
        window.jQuery(() => {
            window.jQuery('.glpi_tabs').on('tabsload', (event, ui) => {
                initConfiguredTables(ui.panel && ui.panel[0] ? ui.panel[0] : document);
            });
        });

        window.jQuery(document).ajaxComplete(() => initConfiguredTables(document));
    }
})(window, document);

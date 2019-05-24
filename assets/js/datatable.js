import $ from 'jquery'
import bootbox from 'bootbox'
import moment from 'moment'
import Snackbar from 'node-snackbar'
import 'datatables.net'
import 'datatables.net-bs4'

const browser = (name, version) => {
    let icon = 'browser';
    if (name.indexOf('Chrome') >= 0) {
        icon = 'chrome'
    } else if (name.indexOf('BlackBerry') >= 0) {
        icon = 'blackberry'
    } else if (name.indexOf('Edge') >= 0) {
        icon = 'edge'
    } else if (name.indexOf('Firefox') >= 0) {
        icon = 'firefox'
    } else if (name.indexOf('IE') >= 0) {
        icon = 'internet-explorer'
    } else if (name.indexOf('Netscape') >= 0) {
        icon = 'netscape'
    } else if (name.indexOf('Opera') >= 0) {
        icon = 'opera'
    } else if (name.indexOf('Safari') >= 0) {
        icon = 'safari'
    }
    return `<img alt="${name}" data-toggle="tooltip" height="32" src="${icons[icon]}" title="${name} ${version}">`;
};

const e = str => {
    const replacements = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        "'": '&#039;',
        '"': '&quot;'
    };
    return str.replace(/[&<>"']/g, match => replacements[match])
};

const platform = (name, version) => {
    let icon = 'platform';
    if (name.indexOf('Android') >= 0) {
        icon = 'android'
    } else if (name.indexOf('BlackBerry') >= 0) {
        icon = 'blackberry'
    } else if (name.indexOf('BSD') >= 0) {
        icon = 'bsd'
    } else if ((name.indexOf('iOS') >= 0) || (name.indexOf('Mac') >= 0)) {
        icon = 'apple'
    } else if (name.indexOf('Linux') >= 0) {
        icon = 'linux'
    } else if (name.indexOf('Ubuntu') >= 0) {
        icon = 'ubuntu'
    } else if (name.indexOf('Windows') >= 0) {
        if (['8', '8.1', '10'].indexOf(version) >= 0) {
            icon = 'windows'
        } else {
            icon = 'windows-old'
        }
    }
    return `<img alt="${name}" data-toggle="tooltip" height="32" src="${icons[icon]}" title="${name} ${version}">`
};

const render = (format, data, row, options) => {
    switch (format) {
        case 'actions':
            return options.actions.map(action => {
                switch (action) {
                    case 'remove':
                        return `<a class="btn btn-warning btn-sm" data-confirm="${options.confirmation}" data-toggle="tooltip" href="${options.base}/${data}/remove" title="${translations['Remove']}"><i class="fas fa-trash-alt fa-fw"></i><span class="d-none">${translations['Remove']}</span></a>`;
                    case 'update':
                        return `<a class="btn btn-info btn-sm" data-loading data-toggle="tooltip" href="${options.base}/${data}/update" title="${translations['Edit']}"><i class="fas fa-pen-alt fa-fw"></i></a>`;
                    default:
                        return `<a class="btn btn-secondary btn-sm" data-loading href="${options.base}/${data}"><i class="fas fa-eye mr-1"></i> ${translations['Details']}</a>`;
                }
            }).join(' ');
        case 'email':
            return `<a href="mailto:${data}" target="_blank">${data}</a>`;
        case 'link': {
            let display = e(row.l_metaTitle || row.l_target);
            if (display.length >= 32) {
                display = display.substring(0, 32) + '&hellip;'
            }
            return `<a href="${options.base}/${data}">${display}</a>`;
        }
        case 'roles':
            return data.map(role => `<span class="badge badge-light text-lowercase">${translations[role]}</span>`).join(' ');
        case 'selector':
            return `<div class="custom-control custom-checkbox">
              <input class="custom-control-input" id="selector-${data}" name="selection" type="checkbox" value="${data}">
              <label class="custom-control-label" for="selector-${data}"></label>
            </div>`;
        case 'target': {
            let display = e(row.l_metaTitle || row.l_target);
            if (display.length >= 32) {
                display = display.substring(0, 32) + '&hellip;'
            }
            return `<a href="${data}" target="_blank">${display}</a>`;
        }
        case 'timestamp':
            if (data) {
                const m = moment(data);
                const tooltip = m.format(options.format || 'DD/MM/YYYY HH:mm');
                const display = m.fromNow();
                let clazz;
                if ((options.data.indexOf('expires') >= 0) && m.isBefore(moment())) {
                    clazz = 'text-danger';
                }
                return `<span class="${clazz}" data-toggle="tooltip" title="${tooltip}">${display}</span>`;
            }
            return `<span class="text-muted">${translations['n/a']}</span>`;
        case 'ua':
            return [
                `<img alt="${row.c_ipAddress}" data-toggle="tooltip" height="32" src="${icons['ip']}" title="${row.c_ipAddress}">`,
                browser(row.c_browserName, row.c_browserVersion),
                platform(row.c_platformName, row.c_platformVersion),
                `<img alt="${row.c_device}" data-toggle="tooltip" height="32" src="${icons['computer']}" title="${row.c_device}">`
            ].join(' ');
        case 'user':
            if (data) {
                return `<a href="${options.base}/${data}">${row.u_name}</a>`;
            }
            return `<span class="text-muted">${translations['Anonymous']}</span>`;
        default:
            return data
    }
};

const wrapper = function () {
    return $(this).each(function () {
        const $wrapper = $(this);
        const $table = $wrapper.find('table');
        const $toolbar = $wrapper.find('.btn-toolbar');
        const columns = [];
        $table.find('thead th[data-column]').each(function () {
            const column = $(this).data('column');
            columns.push({
                data: column.data,
                name: column.name || column.data,
                orderable: !!column.orderable,
                render(data, type, row) {
                    const safe = typeof data === 'string' ? e(data) : data;
                    if (type === 'display') {
                        return render(column.render || column.name || column.data, safe, row, column)
                    }
                    return data
                },
                searchable: !!column.searchable
            })
        });
        $table.on('error.dt', (a, b, c, message) => console.error(message));
        $table.on('preXhr.dt', function () {
            $table.find('input:checkbox').prop('checked', false);
            const $tools = $toolbar.find('button[data-tool]');
            $tools.prop('disabled', true)
                .filter('[data-tool="reload"]')
                .find('i.fas').addClass('fa-spin')
        });
        $table.on('xhr.dt', function () {
            const $tools = $toolbar.find('button[data-tool]');
            $tools.prop('disabled', false)
                .filter('[data-tool="reload"]')
                .find('i.fas').removeClass('fa-spin')
        });
        const $datatable = $table.DataTable({
            autoWidth: false,
            classes: {
                sFilterInput: 'form-control',
                sLengthSelect: 'form-control',
                sWrapper: 'dataTables_wrapper dt-bootstrap4'
            },
            dom:
                "<'card-body pt-0'<'d-flex'<'mr-auto'l>f>>" +
                "<'table-responsive'tr>" +
                "<'card-body'p>" +
                "<'card-footer'i>",
            columns: columns,
            language: {
                emptyTable: 'No data found to display.',
                info: '_START_ &ndash; _END_ of _TOTAL_ items shown.',
                infoEmpty: '0 of 0 items shown.',
                lengthMenu: '_MENU_',
                search: '',
                searchPlaceholder: 'Search...',
                zeroRecords: 'No data matches your search.'
            },
            order: $table.data('order') || [[columns.length - 1, 'desc']],
            pagingType: 'simple_numbers',
            processing: false,
            rowCallback: row => $('[data-toggle="tooltip"]', row).tooltip({ container: 'body' }),
            rowId: row => 'row-' + row.id,
            searchDelay: 250,
            serverSide: true
        });
        $table.find('thead input:checkbox').on('change', function () {
            $table.find('input[name="selection"]').prop(
                'checked', $(this).is(':checked')
            )
        });
        $toolbar.on('click', 'button[data-tool]', function () {
            const $btn = $(this);
            const tool = $btn.data('tool');
            if (tool === 'reload') {
                $datatable.ajax.reload(null, false)
            } else if (tool === 'remove') {
                const ids = [];
                $table.find('input[name="selection"]:checked').each((i, el) => ids.push($(el).val()));
                if (ids.length === 0) {
                    Snackbar.show({
                        pos: 'bottom-right',
                        text: $btn.data('message')
                    })
                } else {
                    bootbox.confirm({
                        buttons: {
                            cancel: {
                                label: translations['Cancel'],
                                className: 'btn-light'
                            },
                            confirm: {
                                label: $btn.html(),
                                className: 'btn-danger'
                            }
                        },
                        callback(yes) {
                            if (yes) {
                                $btn.prop('disabled', true)
                                    .find('i.fas')
                                    .attr('class', 'fas fa-circle-notch fa-spin');
                                $.post($btn.data('url'), { ids, _method: 'DELETE' }, response => {
                                    Snackbar.show({
                                        pos: 'bottom-right',
                                        text: response.message
                                    });
                                    $datatable.ajax.reload(null, false)
                                }).always(() => {
                                    $btn.prop('disabled', false)
                                        .find('i.fas')
                                        .attr('class', 'fas fa-trash-alt');
                                })
                            }
                        },
                        centerVertical: true,
                        message: $btn.data('confirm').replace('%count%', ids.length)
                    })
                }
            }
        })
    })
};

$.fn.datatable = wrapper;

export default wrapper

import '../scss/app.scss'

import $ from 'jquery'
import { edit } from 'ace-builds/src-noconflict/ace'
import 'ace-builds/src-noconflict/mode-html'
import 'ace-builds/src-noconflict/theme-textmate'
import 'ace-builds/webpack-resolver'
import bootbox from 'bootbox'
import Chartist from 'chartist'
import ClipboardJS from 'clipboard'
import 'popper.js'
import 'bootstrap'
import 'chartist-plugin-tooltips'
import 'jquery-datetimepicker'

import './datatable'

const REGEXP_BUTTON_VARIANT = /(?:^|\s)(btn-(outline-)?(danger|dark|info|light|primary|warning|secondary|success))(?:\s|$)/;
const REGEXP_FA_ICON = /^(fa[brs]\s(fa-[^\s$]+)+)(.*)$/;

const animation = function (data, seq, delays, durations) {
    seq++;
    if (data.type === 'area') {
        data.element.animate({
            d: {
                begin: seq * delays + 1000,
                dur: durations,
                from: data.path.clone().scale(1, 0).translate(0, data.chartRect.height()).stringify(),
                to: data.path.clone().stringify(),
                easing: 'easeOutQuint'
            }
        })
    } else if (data.type === 'grid') {
        data.element.animate({
            [data.axis.units.pos + '1']: {
                begin: seq * delays,
                dur: durations,
                from: data[data.axis.units.pos + '1'] - 30,
                to: data[data.axis.units.pos + '1'],
                easing: 'easeOutQuart'
            },
            [data.axis.units.pos + '2']: {
                begin: seq * delays,
                dur: durations,
                from: data[data.axis.units.pos + '2'] - 100,
                to: data[data.axis.units.pos + '2'],
                easing: 'easeOutQuart'
            },
            opacity: {
                begin: seq * delays,
                dur: durations,
                from: 0,
                to: 1,
                easing: 'easeOutQuart'
            }
        })
    } else if (data.type === 'line') {
        data.element.animate({
            d: {
                begin: seq * delays + 1000,
                dur: durations,
                from: data.path.clone().scale(1, 0).translate(0, data.chartRect.height()).stringify(),
                to: data.path.clone().stringify(),
                easing: 'easeOutQuint'
            },
            opacity: {
                begin: seq * delays + 1000,
                dur: durations,
                from: 0,
                to: 1
            }
        })
    } else if (data.type === 'label') {
        if (data.axis === 'x') {
            data.element.animate({
                y: {
                    begin: seq * delays + 500,
                    dur: durations,
                    from: data.y + 100,
                    to: data.y,
                    easing: 'easeOutQuart'
                }
            })
        } else {
            data.element.animate({
                x: {
                    begin: seq * delays + 500,
                    dur: durations,
                    from: data.x - 100,
                    to: data.x,
                    easing: 'easeOutQuart'
                }
            })
        }
    } else if (data.type === 'point') {
        data.element.animate({
            opacity: {
                begin: seq * delays + 750,
                dur: durations,
                from: 0,
                to: 1,
                easing: 'easeOutQuart'
            },
            x1: {
                begin: seq * delays + 750,
                dur: durations,
                from: data.x - 100,
                to: data.x,
                easing: 'easeOutQuart'
            }
        })
    }
};

$(document).ready(function () {
    $(document).on('click', 'a[data-copy]', e => e.preventDefault());
    const clipboard = new ClipboardJS('a[data-copy]', {
        text(trigger) {
            return trigger.getAttribute('href')
        }
    });
    clipboard.on('success', e => {
        $(e.trigger)
            .attr('data-original-title', 'Copied!')
            .tooltip('show')
            .removeAttr('data-original-title')
    });
    $(document).on('click', 'a[data-confirm]', function (e) {
        e.preventDefault();
        const $el = $(this);
        const $icon = $el.find('i.fas');
        const variant = REGEXP_BUTTON_VARIANT.exec($el.attr('class'));
        bootbox.confirm({
            buttons: {
                cancel: {
                    label: translations['Cancel'],
                    className: 'btn-light'
                },
                confirm: {
                    label: $el.text(),
                    className: variant !== null ? variant[1] : 'btn-primary'
                }
            },
            callback(yes) {
                if (yes) {
                    $el.addClass('disabled').prop('disabled', true);
                    $icon.attr('class', $icon.attr('class').replace(REGEXP_FA_ICON, 'fas fa-circle-notch fa-spin $3'));
                    location.href = $el.attr('href')
                }
            },
            centerVertical: true,
            message: $el.data('confirm')
        })
    });
    $(document).on('click', 'a[data-loading], button[data-loading]', function () {
        const $el = $(this);
        const $icon = $el.find('i.fas');
        $el.addClass('disabled').prop('disabled', true);
        $icon.attr('class', $icon.attr('class').replace(REGEXP_FA_ICON, 'fas fa-circle-notch fa-spin $3'))
    });
    $('form').on('submit', function () {
        const $el = $(this).find('button[type="submit"]');
        const $icon = $el.find('i.fas');
        $el.addClass('disabled').prop('disabled', true);
        $icon.attr('class', $icon.attr('class').replace(REGEXP_FA_ICON, 'fas fa-circle-notch fa-spin $3'))
    });
    $('[data-chart="clicks"]').each(function () {
        const $chart = $(this);
        const url = $chart.data('url');
        $.get(url, response => {
            const chart = new Chartist.Line($chart.get(0), response, {
                axisX: { onlyInteger: true },
                axisY: { onlyInteger: true },
                lineSmooth: false,
                low: 0,
                plugins: [
                    Chartist.plugins.tooltip()
                ],
                showArea: true
            });
            let seq = 0,
                delays = 80,
                durations = 500;
            chart.on('created', () => seq = 0);
            chart.on('draw', data => animation(data, seq, delays, durations))
        })
    });
    $('[data-chart="popular"]').each(function () {
        const $chart = $(this);
        const url = $chart.data('url');
        $.get(url, response => {
            const chart = new Chartist.Bar($chart.get(0), response, {
                axisX: { onlyInteger: true },
                axisY: {
                    offset: 70,
                    onlyInteger: true
                },
                horizontalBars: true,
                lineSmooth: false,
                low: 0,
                plugins: [
                    Chartist.plugins.tooltip()
                ],
                reverseData: true,
                seriesBarDistance: 10
            });
            let seq = 0,
                delays = 80,
                durations = 500;
            chart.on('created', () => seq = 0);
            chart.on('draw', data => animation(data, seq, delays, durations))
        })
    });
    $('[data-toggle="tooltip"]').tooltip({ container: 'body' });
    $('[data-widget="ace"]').each(function () {
        const $textarea = $(this).addClass('d-none');
        const $ace = $('<div></div>')
            .attr('style', 'width: 100%; height: 240px')
            .insertBefore($textarea);
        const editor = edit($ace.get(0));
        editor.session.setMode('ace/mode/html');
        editor.getSession().on('change', () => $textarea.val(editor.getSession().getValue()));
        editor.setTheme('ace/theme/textmate');
        editor.setValue($textarea.val(), -1)
    });
    $('[data-widget="datatable"]').datatable();
    $('[data-widget="datetimepicker"]')
        .attr('autocomplete', 'off')
        .datetimepicker({ format: 'Y-m-d H:i:s' })
});

<?php
/**
 * Plugin Name: Company Directory Shortcode
 * Description: Displays a company directory from Google Sheets via Apps Script. Use shortcode [company_directory].
 * Version: 1.0
 * Author: Your Name
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// ─── CONFIGURATION ────────────────────────────────────────────────────────────
// Paste your Google Apps Script deployment URL below
define( 'CDIRECTORY_APPS_SCRIPT_URL', 'https://script.google.com/macros/s/AKfycbwfE6ZpKwpjmivtPU5Mx9bddgyDAEPSdMlBTDUbW2I0bh-59AdeUtn2_jnp4mnMHcEm/exec' );
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Enqueue scripts and styles only when shortcode is present on the page
 */
function cdirectory_enqueue_assets() {
    global $post;
    if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'company_directory' ) ) {

        // Google Fonts
        wp_enqueue_style(
            'cdirectory-roboto',
            'https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap',
            array(), null
        );

        // DataTables CSS
        wp_enqueue_style(
            'cdirectory-datatables-css',
            'https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css',
            array(), '1.13.6'
        );

        // jQuery (WordPress ships with it)
        wp_enqueue_script( 'jquery' );

        // DataTables JS
        wp_enqueue_script(
            'cdirectory-datatables',
            'https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js',
            array( 'jquery' ), '1.13.6', true
        );

        // SweetAlert2
        wp_enqueue_script(
            'cdirectory-sweetalert2',
            'https://cdn.jsdelivr.net/npm/sweetalert2@11',
            array(), '11', true
        );
    }
}
add_action( 'wp_enqueue_scripts', 'cdirectory_enqueue_assets' );


/**
 * Shortcode handler — [company_directory]
 */
function cdirectory_shortcode() {
    $url = esc_js( CDIRECTORY_APPS_SCRIPT_URL );

    ob_start();
    ?>
    <div id="cdirectory-wrap">

      <style>
        #cdirectory-wrap { font-family: 'Roboto', sans-serif; }

        #cdirectory-wrap .cd-filters { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 16px; }
        #cdirectory-wrap .cd-filters .cd-col { flex: 1; min-width: 150px; }
        #cdirectory-wrap .cd-filters .cd-col-btn { flex: 0 0 120px; }

        #cdirectory-wrap select,
        #cdirectory-wrap input#cd-search {
            width: 100%;
            border: none;
            border-bottom: 1px solid #d3cfcf;
            border-radius: 0;
            font-size: 15px;
            padding: 6px 4px;
            background: transparent;
            outline: none;
            box-shadow: none;
            font-family: 'Roboto', sans-serif;
        }
        #cdirectory-wrap select:focus,
        #cdirectory-wrap input#cd-search:focus { border-color: #704BD8; }

        #cdirectory-wrap button#cd-clear {
            width: 100%;
            padding: 7px 12px;
            background: linear-gradient(to right, #704BD8, #2C1767);
            color: #fff;
            border: none;
            border-radius: 3px;
            font-size: 15px;
            cursor: pointer;
            font-family: 'Roboto', sans-serif;
        }
        table.dataTable.display>tbody>tr.even>.sorting_1,table.dataTable.display>tbody>tr.odd>.sorting_1, table.dataTable.order-table.dataTable.display>tbody>tr.odd>.sorting_1, table.dataTable.order-column.stripe>tbody>tr.odd>.sorting_1, table.dataTable.display>tbody>tr.odd>.sorting_1, table.dataTable.order-table.dataTable.display>tbody>tr.odd>.sorting_1, table.dataTable.order-column.stripe>tbody>tr.odd>.sorting_1,table.dataTable.stripe>tbody>tr.odd>*, table.dataTable.display>tbody>tr.odd>*, table.dataTable.order-column.stripe>tbody>tr.even>.sorting_1{
            box-shadow: none !important;
        }
        table tbody>tr:nth-child(odd)>td, table tbody>tr:nth-child(odd)>th {
    background-color: transparent;
}
        #cdirectory-wrap button#cd-clear:hover { opacity: 0.88; }

        #cdirectory-wrap table#cd-table thead { box-shadow: 0 8px 6px -6px #d6cece; }
        #cdirectory-wrap table.dataTable thead th { padding: 10px 18px; border-bottom: none; }
        #cdirectory-wrap table.dataTable tbody td { padding: 7px 10px; }
        #cdirectory-wrap table.dataTable.no-footer { border-bottom: none; }
        #cdirectory-wrap .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: linear-gradient(to right, #704BD8, #2C1767) !important;
            color: #fff !important;
            border-color: transparent !important;
        }
        #cdirectory-wrap td, #cdirectory-wrap th {
            border: none !important;
            border-bottom: 1px solid #ebebeb !important;
        }
        #cdirectory-wrap table#cd-table {
            border: none;
            border-spacing: 0 10px;
            border-collapse: separate;
        }

        #cdirectory-wrap .cd-info-icon {
            cursor: pointer;
            color: #333;
            font-size: 22px;
            display: block;
            text-align: center;
        }
        #cdirectory-wrap .cd-info-icon:hover { color: #704BD8; }

        #cd-loading {
            text-align: center;
            padding: 40px;
            font-size: 16px;
            color: #666;
        }
        .cd-spinner {
            display: inline-block;
            width: 26px; height: 26px;
            border: 3px solid #ddd;
            border-top-color: #704BD8;
            border-radius: 50%;
            animation: cd-spin 0.8s linear infinite;
            margin-right: 8px;
            vertical-align: middle;
        }
        @keyframes cd-spin { to { transform: rotate(360deg); } }

        /* SweetAlert2 overrides */
        .swal2-popup {
            border: 1px solid #ccc !important;
            border-radius: 5px !important;
            padding: 17px !important;
        }
        .swal2-title {
            color: #000 !important;
            font-size: 1.3em !important;
            font-weight: 500 !important;
            text-align: left !important;
            padding: 0 !important;
            margin-bottom: 10px !important;
        }
        .swal2-html-container { padding: 0 !important; text-align: left !important; }
        .swal2-html-container table { border-collapse: collapse; width: 100%; }
        table.dataTable.display>tbody>tr.odd>.sorting_1, table.dataTable.order-table.dataTable.display>tbody>tr.odd>.sorting_1, table.dataTable.order-column.stripe>tbody>tr.odd>.sorting_1 {
    box-shadow: none !important;
}
        .swal2-html-container th,
        .swal2-html-container td {
            padding: 8px;
            font-size: 14px;
            line-height: 25px;
            border-bottom: 1px solid #f0f0f0 !important;
            border-left:0px;
            border-right:0px;
            border-top:0px;
            color: #000 !important;
            min-width: 120px;
        }
        .swal2-html-container th { font-weight: 600; }
        .swal2-html-container td a { color: #704BD8; }
        .swal2-actions { display: none !important; }
        .swal2-close { color: #000 !important; font-size: 1.5em !important; }
        .cd-swal-popup { max-width: 90vw !important; max-height: 90vh; overflow-y: auto; margin-top: 80px !important; }
        button.swal2-close:focus { background-color: transparent; }
        table.dataTable.display>tbody>tr.odd>.sorting_1, table.dataTable.order-table.dataTable.display>tbody>tr.odd>.sorting_1, table.dataTable.order-column.stripe>tbody>tr.odd>.sorting_1 {
            box-shadow: none !important;
        }
        table.dataTable.display tbody tr:hover>.sorting_1, table.dataTable.order-column.hover tbody tr:hover>.sorting_1 {
            box-shadow: inset 0 0 0 9999px rgb(0 0 0 / 3%) !important;
        }

        @media (max-width: 600px) {
            #cdirectory-wrap .cd-filters .cd-col { flex: 0 0 48%; }
            #cdirectory-wrap .cd-filters .cd-col-btn { flex: 0 0 100%; }
        }
      </style>

      <!-- Filters -->
      <div class="cd-filters">
        <div class="cd-col">
          <input type="text" id="cd-search" placeholder="Search...">
        </div>
        <div class="cd-col">
          <select id="cd-filter-type"><option value="">Filter by Type</option></select>
        </div>
        <div class="cd-col">
          <select id="cd-filter-region"><option value="">Filter by Region</option></select>
        </div>
        <div class="cd-col">
          <select id="cd-filter-coverage"><option value="">Filter by Coverage</option></select>
        </div>
        <div class="cd-col-btn">
          <button id="cd-clear">Clear All</button>
        </div>
      </div>

      <!-- Loading -->
      <div id="cd-loading">
        <span class="cd-spinner"></span> Loading data, please wait...
      </div>

      <!-- Table -->
      <table id="cd-table" class="display" style="width:100%; display:none;">
        <thead>
          <tr>
            <th>Company</th>
            <th>Type</th>
            <th>Phone</th>
            <th>Email</th>
            <th>Info</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>

    </div><!-- #cdirectory-wrap -->

    <script>
    (function($) {
        'use strict';

        var APPS_SCRIPT_URL = '<?php echo $url; ?>';
        var cdTableData = [];

        $(document).ready(function() {

            // Init DataTable
            $('#cd-table').DataTable({
                paging: true,
                pageLength: 10,
                autoWidth: false,
                destroy: true,
                searching: false
            });

            // Load data
            cdLoadData();

            // Filter events
            $('#cd-search').on('input', cdFilterTable);
            $('#cd-filter-type, #cd-filter-region, #cd-filter-coverage').on('change', cdFilterTable);
            $('#cd-clear').on('click', function() {
                $('#cd-search').val('');
                $('#cd-filter-type, #cd-filter-region, #cd-filter-coverage').val('');
                cdFilterTable();
            });

            // Info icon popup
            $(document).on('click', '.cd-info-icon', function() {
                var d = $(this).data();
                var rows = '';

                if (d.contact && d.contact.toString().trim()) {
                    rows += '<tr><th>Contact:</th><td>' + d.contact + '</td></tr>';
                }
                rows += '<tr><th>Phone:</th><td>' + (d.phone || '') + '</td></tr>';
                rows += '<tr><th>Email:</th><td>' + (d.email || '') + '</td></tr>';

                var extra = [];
                if (d.website && d.website.toString().trim()) {
                    var w = d.website.toString().trim();
                    if (!/^https?:\/\//i.test(w)) w = 'https://' + w;
                    extra.push('Website: <a href="' + w + '" target="_blank" rel="noopener">' + w + '</a>');
                }
                if (d.fax   && d.fax.toString().trim())    extra.push('Fax: '    + d.fax);
                if (d.mobile && d.mobile.toString().trim()) extra.push('Mobile: ' + d.mobile);
                if (extra.length) rows += '<tr><th>Additional:</th><td>' + extra.join('<br>') + '</td></tr>';

                if (d.address  && d.address.toString().trim())
                    rows += '<tr><th>Address:</th><td>' + d.address + '</td></tr>';

                if (d.region && d.region.toString().trim())
                    rows += '<tr><th>Region:</th><td>' + d.region.toString().split(',').map(function(v){ return v.trim(); }).join(' , ') + '</td></tr>';

                if (d.coverage && d.coverage.toString().trim())
                    rows += '<tr><th>Coverage:</th><td>' + d.coverage.toString().split(',').map(function(v){ return v.trim(); }).join(', ') + '</td></tr>';

                Swal.fire({
                    title: d.company || '',
                    html: '<table>' + rows + '</table>',
                    showCloseButton: true,
                    focusConfirm: false,
                    position: 'top',
                    grow: 'row',
                    width: '600px',
                    customClass: { popup: 'cd-swal-popup' }
                });
            });
        });

        function cdLoadData() {
            fetch(APPS_SCRIPT_URL)
                .then(function(res) {
                    if (!res.ok) throw new Error('Network error: ' + res.status);
                    return res.json();
                })
                .then(function(result) {
                    if (!result.success) throw new Error(result.error || 'Unknown error');

                    var data = result.data;
                    if (!data || data.length <= 1) {
                        cdShowError('No data found in the sheet.');
                        return;
                    }

                    cdTableData = data;

                    var types = new Set(), regions = new Set(), coverages = new Set();
                    data.slice(1).forEach(function(row) {
                        if (row[1]) types.add(row[1].toString().trim());
                        if (row[4]) row[4].toString().split(',').forEach(function(v) { if (v.trim()) regions.add(v.trim()); });
                        if (row[5]) row[5].toString().split(',').forEach(function(v) { if (v.trim()) coverages.add(v.trim()); });
                    });

                    cdPopulateDropdown('#cd-filter-type',     Array.from(types),     'Type');
                    cdPopulateDropdown('#cd-filter-region',   Array.from(regions),   'Region');
                    cdPopulateDropdown('#cd-filter-coverage', Array.from(coverages), 'Coverage');

                    $('#cd-loading').hide();
                    $('#cd-table').show();
                    cdRenderTable(data);
                })
                .catch(function(err) {
                    cdShowError('Failed to load data: ' + err.message);
                });
        }

        function cdFilterTable() {
            var q        = $('#cd-search').val().toLowerCase().trim();
            var type     = $('#cd-filter-type').val();
            var region   = $('#cd-filter-region').val();
            var coverage = $('#cd-filter-coverage').val();

            // ── Step 1: filter rows matching all active filters ──────────────
            var filtered = cdTableData.filter(function(row, i) {
                if (i === 0) return true;

                var typeMatch = !type || (row[1] && row[1].toString().trim() === type);

                var regionMatch = !region || (row[4] && row[4].toString().split(',').some(function(v) {
                    return v.trim().toLowerCase() === region.toLowerCase();
                }));

                var coverageMatch = !coverage || (row[5] && row[5].toString().split(',').some(function(v) {
                    return v.trim().toLowerCase() === coverage.toLowerCase();
                }));

                var searchMatch = !q || row.some(function(cell) {
                    return cell && cell.toString().toLowerCase().includes(q);
                });

                return typeMatch && regionMatch && coverageMatch && searchMatch;
            });

            // ── Step 2: update OTHER dropdowns based on filtered rows ────────
            // Each dropdown only updates if it is NOT the one that was just used,
            // so the active selection stays intact.

            // Update Type dropdown — based on rows matching region + coverage + search (not type)
            var rowsForType = cdTableData.slice(1).filter(function(row) {
                var regionMatch = !region || (row[4] && row[4].toString().split(',').some(function(v) {
                    return v.trim().toLowerCase() === region.toLowerCase();
                }));
                var coverageMatch = !coverage || (row[5] && row[5].toString().split(',').some(function(v) {
                    return v.trim().toLowerCase() === coverage.toLowerCase();
                }));
                var searchMatch = !q || row.some(function(cell) {
                    return cell && cell.toString().toLowerCase().includes(q);
                });
                return regionMatch && coverageMatch && searchMatch;
            });
            var availableTypes = new Set();
            rowsForType.forEach(function(row) {
                if (row[1]) availableTypes.add(row[1].toString().trim());
            });
            cdPopulateDropdown('#cd-filter-type', Array.from(availableTypes), 'Type');

            // Update Region dropdown — based on rows matching type + coverage + search (not region)
            var rowsForRegion = cdTableData.slice(1).filter(function(row) {
                var typeMatch = !type || (row[1] && row[1].toString().trim() === type);
                var coverageMatch = !coverage || (row[5] && row[5].toString().split(',').some(function(v) {
                    return v.trim().toLowerCase() === coverage.toLowerCase();
                }));
                var searchMatch = !q || row.some(function(cell) {
                    return cell && cell.toString().toLowerCase().includes(q);
                });
                return typeMatch && coverageMatch && searchMatch;
            });
            var availableRegions = new Set();
            rowsForRegion.forEach(function(row) {
                if (row[4]) row[4].toString().split(',').forEach(function(v) {
                    if (v.trim()) availableRegions.add(v.trim());
                });
            });
            cdPopulateDropdown('#cd-filter-region', Array.from(availableRegions), 'Region');

            // Update Coverage dropdown — based on rows matching type + region + search (not coverage)
            var rowsForCoverage = cdTableData.slice(1).filter(function(row) {
                var typeMatch = !type || (row[1] && row[1].toString().trim() === type);
                var regionMatch = !region || (row[4] && row[4].toString().split(',').some(function(v) {
                    return v.trim().toLowerCase() === region.toLowerCase();
                }));
                var searchMatch = !q || row.some(function(cell) {
                    return cell && cell.toString().toLowerCase().includes(q);
                });
                return typeMatch && regionMatch && searchMatch;
            });
            var availableCoverages = new Set();
            rowsForCoverage.forEach(function(row) {
                if (row[5]) row[5].toString().split(',').forEach(function(v) {
                    if (v.trim()) availableCoverages.add(v.trim());
                });
            });
            cdPopulateDropdown('#cd-filter-coverage', Array.from(availableCoverages), 'Coverage');

            // ── Step 3: render filtered rows ─────────────────────────────────
            cdRenderTable(filtered);
        }

        function cdRenderTable(data) {
            var table = $('#cd-table').DataTable();
            table.clear();

            data.forEach(function(row, i) {
                if (i === 0) return;
                if (!row || row.every(function(c) { return !c || c.toString().trim() === ''; })) return;

                var company  = row[0]  || '';
                var type     = row[1]  || '';
                var phone    = row[2]  || '';
                var email    = row[3]  || '';
                var region   = row[4]  || '';
                var coverage = row[5]  || '';
                var address  = row[6]  || '';
                var contact  = row[7]  || '';
                var fax      = row[8]  || '';
                var website  = row[9]  || '';
                var mobile   = row[10] || '';

                var icon = '<span class="cd-info-icon"'
                    + ' data-company="'  + cdEsc(company)  + '"'
                    + ' data-phone="'    + cdEsc(phone)    + '"'
                    + ' data-email="'    + cdEsc(email)    + '"'
                    + ' data-region="'   + cdEsc(region)   + '"'
                    + ' data-coverage="' + cdEsc(coverage) + '"'
                    + ' data-address="'  + cdEsc(address)  + '"'
                    + ' data-contact="'  + cdEsc(contact)  + '"'
                    + ' data-fax="'      + cdEsc(fax)      + '"'
                    + ' data-website="'  + cdEsc(website)  + '"'
                    + ' data-mobile="'   + cdEsc(mobile)   + '">ⓘ</span>';

                table.row.add([company, type, phone, email, icon]);
            });

            table.draw();
        }

        function cdPopulateDropdown(selector, values, label) {
            var $el = $(selector);
            var selected = $el.val();
            $el.empty().append('<option value="">Filter by ' + label + '</option>');
            values.filter(Boolean).sort().forEach(function(v) {
                $el.append('<option value="' + cdEsc(v) + '"' + (v === selected ? ' selected' : '') + '>' + cdEsc(v) + '</option>');
            });
        }

        function cdEsc(str) {
            return str.toString()
                .replace(/&/g,  '&amp;')
                .replace(/"/g,  '&quot;')
                .replace(/'/g,  '&#39;')
                .replace(/</g,  '&lt;')
                .replace(/>/g,  '&gt;');
        }

        function cdShowError(msg) {
            $('#cd-loading').html('<p style="color:red;">&#9888; ' + msg + '</p>');
        }

    })(jQuery);
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode( 'company_directory', 'cdirectory_shortcode' );
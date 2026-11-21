<?php
// INCLUE FUNCOES DE ADDONS -----------------------------------------------------------------------
include('addons.class.php');
require_once __DIR__ . '/loja_lib.php';
require_once __DIR__ . '/acrescimos_lib.php';
require_once __DIR__ . '/periodos_lib.php';
$rc_quick_periods = rc_cash_periods();

// VERIFICA SE O USUARIO ESTA LOGADO --------------------------------------------------------------
session_name('mka');
if (!isset($_SESSION)) session_start();
if (!isset($_SESSION['mka_logado']) && !isset($_SESSION['MKA_Logado'])) exit('Acesso negado... <a href="/admin/login.php">Fazer Login</a>');
// VERIFICA SE O USUARIO ESTA LOGADO --------------------------------------------------------------

$manifestTitle = $Manifest->{'name'} ?? '';
$manifestVersion = $Manifest->{'version'} ?? '';
?>

<!DOCTYPE html>
<html lang="pt-BR" id="rc-caixa-page" class="has-navbar-fixed-top">
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta charset="iso-8859-1">
<title>MK-AUTH :: <?php echo $Manifest->{'name'}; ?></title>

<link href="../../estilos/mk-auth.css" rel="stylesheet" type="text/css" />
<link href="../../estilos/font-awesome.css" rel="stylesheet" type="text/css" />
<link href="../../estilos/bi-icons.css" rel="stylesheet" type="text/css" />

<script src="../../scripts/jquery.js"></script>
<script src="../../scripts/mk-auth.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<link href="navbar_layout.css" rel="stylesheet" type="text/css" />
<style>
    #rc-caixa .rc-shortcuts { display: grid; grid-template-columns: repeat(4,minmax(0,1fr)); gap: 12px; max-width: none; margin: 4px auto 18px; }
    #rc-caixa .rc-shortcut-card { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 6px; min-height: 76px; padding: 14px; border: 1px solid #9ec9ed; border-radius: 7px; background: #edf6ff; color: #075a9e; text-decoration: none; text-align: center; cursor: pointer; font-family: inherit; }
    #rc-caixa .rc-shortcut-card strong { font-size: 14px; }
    #rc-caixa .rc-shortcut-card span { font-size: 12px; color: #315d80; }
    #rc-caixa .rc-shortcut-card:hover, #rc-caixa .rc-shortcut-card:focus-visible { background: #dceeff; outline: 2px solid #1685d1; outline-offset: 2px; }
    #rc-caixa .rc-conference { scroll-margin-top: calc(var(--rc-navbar-height, 3.25rem) + 16px); }
    @media(max-width:600px) { #rc-caixa .rc-shortcuts { grid-template-columns: 1fr; } }
    @media print { #rc-caixa .rc-shortcuts { display: none !important; } }
    #rc-caixa .finance-table tfoot th, #rc-caixa .finance-table tfoot td { background: #edf5ff; background-image: none; color: #153b60; text-align: left; border-top: 2px solid #1285cf; font-weight: 700; padding: 12px 10px; white-space: nowrap; }
    #rc-caixa .finance-table tfoot td, #rc-caixa .finance-table tbody td:nth-child(6), #rc-caixa .finance-table tbody td:nth-child(7), #rc-caixa .finance-table tbody td:nth-child(8) { text-align: right; font-variant-numeric: tabular-nums; }
    #rc-caixa .finance-table tfoot #rc-footer-extra { color: #087f4f; }
    #rc-caixa .finance-table tfoot #rc-footer-outgoing { color: #dc2626; }
    #rc-caixa .rc-extras-card .stat-value { font-size: 17px; color: #087f4f; }
    #rc-caixa .rc-extra-paid { color: #087f4f; background: #d1fae5; font-weight: 600; border-radius: 4px; padding: 3px 6px; display: inline-block; }
    @media(max-width:900px) { #rc-caixa #content-financeiro .rc-cash-stats { grid-template-columns: repeat(2,minmax(0,1fr)); } }

    :root {
        --primary: #6e7aff;
        --primary-dark: #4ce65b;
        --success: #10B981;
        --danger: #EF4444;
        --info: #3B82F6;
        --warning: #F59E0B;
        --dark: #1F2937;
        --border: #E5E7EB;
        --bg-light: #F9FAFB;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: linear-gradient(135deg, #fffff 0%, #764ba2 100%);
        color: #333;
        line-height: 1.5;
    }

    .container {
        width: 100%;
        max-width: 10000px;
        margin: 15px auto;
        background: white;
        border-radius: 10px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        overflow: hidden;
    }

    /* Breadcrumb */
    .breadcrumb {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        padding: 12px 20px;
    }

    .breadcrumb ul {
        display: flex;
        list-style: none;
        gap: 8px;
        font-size: 13px;
        color: white;
    }

    .breadcrumb a {
        color: rgba(255,255,255,0.9);
        text-decoration: none;
    }

    .breadcrumb a:hover {
        color: white;
    }

    .breadcrumb .is-active a {
        color: white;
        font-weight: 600;
    }

    /* TABS SYSTEM */
    .tabs-container {
        background: white;
        border-bottom: 2px solid var(--border);
    }

    .tabs {
        display: flex;
        padding: 0 20px;
        gap: 0;
        align-items: center;
        justify-content: flex-start;
    }

    .tab-button {
        padding: 15px 25px;
        background: transparent;
        border: none;
        border-bottom: 3px solid transparent;
        cursor: pointer;
        font-size: 14px;
        font-weight: 600;
        color: #6B7280;
        transition: all 0.3s;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: flex;
        align-items: center;
        gap: 8px;
        position: relative;
    }

    .tab-button:not(:last-child)::after {
        content: '|';
        position: absolute;
        right: 0;
        top: 50%;
        transform: translateY(-50%);
        color: #D1D5DB;
        font-size: 18px;
        font-weight: 300;
        padding: 0 10px;
    }

    .tab-button:hover {
        color: var(--primary);
        background: var(--bg-light);
    }

    .tab-button.active {
        color: var(--primary);
        border-bottom-color: var(--primary);
        background: var(--bg-light);
    }

    .tab-content {
        display: none;
        padding: 20px;
        animation: fadeIn 0.3s ease-in;
    }

    .tab-content.active {
        display: block;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Content */
    .content-wrapper {
        padding: 15px 20px;
    }

    /* Form compacto */
    .search-form {
        background: var(--bg-light);
        border-radius: 8px;
        padding: 12px 15px;
        margin-bottom: 15px;
        border: 1px solid var(--border);
    }

    form {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        gap: 10px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    form label {
        font-weight: 600;
        font-size: 11px;
        color: var(--dark);
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    input[type="text"],
    input[type="date"],
    select {
        padding: 8px 10px;
        border: 1px solid var(--border);
        border-radius: 5px;
        font-size: 13px;
        transition: all 0.2s;
        min-width: 180px;
    }

    input[type="text"]:focus,
    input[type="date"]:focus,
    select:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.1);
    }

    .btn {
        padding: 8px 15px;
        border: none;
        border-radius: 5px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .btn-primary {
        background: var(--primary);
        color: white;
    }

    .btn-primary:hover {
        background: var(--primary-dark);
        transform: translateY(-1px);
    }

    .btn-secondary {
        background: white;
        color: var(--dark);
        border: 1px solid var(--border);
    }

    .btn-secondary:hover {
        background: var(--bg-light);
    }

    /* Stats em linha horizontal - COMPACTO */
    .stats-bar {
        display: flex;
        gap: 15px;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 12px 15px;
        border-radius: 8px;
        margin-bottom: 15px;
        flex-wrap: wrap;
    }

    .stat-item {
        flex: 1;
        min-width: 180px;
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 12px;
        background: white;
        border-radius: 6px;
        border-left: 3px solid;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        cursor: pointer;
        transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
        user-select: none;
    }

    .stat-item:hover,
    .stat-item:focus-visible {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(15, 23, 42, 0.10);
        outline: none;
    }

    .stat-item.active-filter {
        background: #f8fafc;
        box-shadow: 0 0 0 2px currentColor, 0 6px 16px rgba(15, 23, 42, 0.10);
    }

    .client-icon-clean {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 18px;
        height: 18px;
        margin-right: 6px;
        color: #64748b;
        font-size: 16px;
        vertical-align: -2px;
    }

    tr.filter-hidden { display: none !important; }

    .stat-item.boletos { border-left-color: var(--dark); }
    .stat-item.entradas { border-left-color: var(--info); }
    .stat-item.saidas { border-left-color: var(--danger); }
    .stat-item.saldo { border-left-color: var(--success); }

    .stat-icon {
        font-size: 20px;
        opacity: 0.8;
    }

    .stat-content {
        flex: 1;
    }

    .stat-label {
        font-size: 10px;
        font-weight: 600;
        color: #6B7280;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        margin-bottom: 2px;
    }

    .stat-value {
        font-size: 18px;
        font-weight: 700;
    }

    .stat-item.boletos .stat-value { color: var(--dark); }
    .stat-item.entradas .stat-value { color: var(--info); }
    .stat-item.saidas .stat-value { color: var(--danger); }
    .stat-item.saldo .stat-value { color: var(--success); }

    /* Table - COMPACTO como original */
    .table-wrapper {
        background: white;
        border-radius: 8px;
        overflow-x: auto;
        border: 1px solid var(--border);
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    .finance-table { min-width: 1200px; table-layout: auto; }
    .finance-table th, .finance-table td { white-space: normal; vertical-align: middle; }
    .finance-table td:first-child { min-width: 240px; }
    .finance-table td:nth-child(2) { min-width: 150px; overflow-wrap: anywhere; }
    .finance-table td:nth-child(5) { min-width: 300px; overflow-wrap: anywhere; }
    .finance-table td:nth-child(3), .finance-table td:nth-child(n+6) { white-space: nowrap; }
    .finance-table td a { white-space: normal; max-width: 100%; overflow-wrap: anywhere; }

    thead {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        position: sticky;
        top: 0;
        z-index: 10;
    }

    th {
        padding: 12px 10px;
        text-align: left;
        color: white;
        font-weight: 700;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        text-shadow: 0 1px 2px rgba(0,0,0,0.2);
        border-bottom: 3px solid rgba(255,255,255,0.2);
    }

    th:hover {
        background: rgba(255,255,255,0.15);
        cursor: pointer;
        transition: all 0.2s ease;
    }

    tbody tr {
        border-bottom: 1px solid var(--border);
        transition: background 0.15s;
    }

    tbody tr:hover {
        background: var(--bg-light);
    }

    tbody tr.highlight {
        background: #f8f9fa;
    }

    tbody tr.highlight:hover {
        background: #f1f3f5;
    }

    td {
        padding: 9px 8px;
        font-size: 13px;
    }

    td a {
        text-decoration: none;
        color: var(--primary);
        font-weight: 600;
        transition: color 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    td a:hover {
        color: var(--primary-dark);
    }

    .no-data {
        text-align: center;
        padding: 40px 20px;
        color: #9CA3AF;
    }

    .hidden {
        display: none;
    }

    /* Badges */
    .badge {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
    }

    .badge-success {
        background: #D1FAE5;
        color: #065F46;
    }

    .badge-danger {
        background: #FEE2E2;
        color: #991B1B;
    }

    .badge-info {
        background: #DBEAFE;
        color: #1E40AF;
    }

    .icon-sm {
        width: 16px;
        height: 16px;
        vertical-align: middle;
    }

    /* GRAFICOS STYLES */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 20px;
        margin-bottom: 25px;
    }

    .stat-card {
        background: white;
        border-radius: 10px;
        padding: 20px;
        border: 2px solid var(--border);
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        transition: all 0.3s;
        position: relative;
        overflow: hidden;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
    }

    .stat-card.entradas::before { background: var(--success); }
    .stat-card.saidas::before { background: var(--danger); }
    .stat-card.saldo::before { background: var(--info); }
    .stat-card.boletos::before { background: var(--warning); }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 16px rgba(0,0,0,0.12);
    }

    .stat-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 10px;
    }

    .stat-card .stat-icon {
        width: 45px;
        height: 45px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
    }

    .stat-card.entradas .stat-icon { background: rgba(16, 185, 129, 0.1); }
    .stat-card.saidas .stat-icon { background: rgba(239, 68, 68, 0.1); }
    .stat-card.saldo .stat-icon { background: rgba(59, 130, 246, 0.1); }
    .stat-card.boletos .stat-icon { background: rgba(245, 158, 11, 0.1); }

    .stat-info {
        flex: 1;
    }

    .stat-card .stat-label {
        font-size: 12px;
        font-weight: 600;
        color: #6B7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stat-card .stat-value {
        font-size: 26px;
        font-weight: 700;
        margin-top: 5px;
    }

    .stat-card.entradas .stat-value { color: var(--success); }
    .stat-card.saidas .stat-value { color: var(--danger); }
    .stat-card.saldo .stat-value { color: var(--info); }
    .stat-card.boletos .stat-value { color: var(--warning); }

    .stat-trend {
        font-size: 11px;
        margin-top: 5px;
        color: #6B7280;
    }

    /* Chart Containers */
    .charts-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
        gap: 20px;
        margin-bottom: 20px;
    }

    .chart-container {
        background: white;
        border-radius: 10px;
        padding: 20px;
        border: 1px solid var(--border);
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }

    .chart-header {
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid var(--border);
    }

    .chart-title {
        font-size: 16px;
        font-weight: 700;
        color: var(--dark);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .chart-subtitle {
        font-size: 12px;
        color: #6B7280;
        margin-top: 5px;
    }

    .chart-wrapper {
        position: relative;
        height: 350px;
    }

    /* Monthly Table */
    .monthly-table {
        background: white;
        border-radius: 10px;
        padding: 20px;
        border: 1px solid var(--border);
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        margin-top: 20px;
    }

    .monthly-table table {
        width: 100%;
        border-collapse: collapse;
    }

    .monthly-table thead {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    }

    .monthly-table th {
        padding: 12px;
        color: white;
        font-weight: 700;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        text-align: left;
    }

    .monthly-table td {
        padding: 12px;
        border-bottom: 1px solid var(--border);
        font-size: 13px;
    }

    .monthly-table tbody tr:hover {
        background: var(--bg-light);
    }

    /* Filter Section */
    .filter-section {
        background: var(--bg-light);
        border-radius: 8px;
        padding: 15px 20px;
        margin-bottom: 20px;
        border: 1px solid var(--border);
        display: flex;
        gap: 15px;
        align-items: center;
        flex-wrap: wrap;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .filter-group label {
        font-weight: 600;
        font-size: 11px;
        color: var(--dark);
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .container {
            width: 100%;
            margin: 0;
            border-radius: 0;
        }

        .content-wrapper {
            padding: 12px;
        }

        form {
            flex-direction: column;
            align-items: stretch;
        }

        .form-group {
            width: 100%;
        }

        input[type="text"],
        input[type="date"] {
            width: 100%;
        }

        .stats-bar {
            flex-direction: column;
        }

        .stat-item {
            min-width: 100%;
        }

        table {
            font-size: 11px;
        }

        th, td {
            padding: 6px 4px;
        }

        .charts-grid {
            grid-template-columns: 1fr;
        }

        .chart-wrapper {
            height: 300px;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }

        .tabs {
            padding: 0 10px;
            overflow-x: auto;
        }

        .tab-button {
            padding: 12px 20px;
            font-size: 12px;
            white-space: nowrap;
        }
    }
    .rc-conference { margin: 18px 0; padding: 16px; border: 1px solid var(--border); border-radius: 8px; }
    .rc-conference p, .rc-tools { margin: 10px 0; font-size: 13px; }
    .rc-conference summary { padding: 10px 0; cursor: pointer; font-weight: 600; }
    .rc-summary-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 12px; }
    .rc-conference td { overflow-wrap: anywhere; }
    .rc-print-period { font-size: 12px; margin: 10px 0; }
    @media print {
        @page { size: A4 landscape; margin: 10mm; }
        body, .container { background: white !important; box-shadow: none; margin: 0; overflow: visible; }
        nav, .navbar, .breadcrumb, .tabs, .search-form, .rc-tools, #content-graficos, #content-ticket, #content-evolucao, #content-anomalias { display: none !important; }
        #content-financeiro { display: block !important; }
        .table-wrapper { overflow: visible; }
        .finance-table { min-width: 0; table-layout: auto; }
        .finance-table td:first-child, .finance-table td:nth-child(2), .finance-table td:nth-child(5) { min-width: 0; }
        th, td { font-size: 9px !important; padding: 4px !important; color: black !important; }
        thead { display: table-header-group; position: static; }
        tr { break-inside: avoid; }
        .rc-summary-grid { display: block; }
    }

    /* Visual da conferência: escopo limitado ao addon. */
    .container { --primary: #2563eb; --primary-dark: #1d4ed8; --success: #059669; width: calc(100% - 32px); max-width: none; margin: 24px auto; border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: 0 8px 32px #0f172a08; color: #334155; background: #fff; }
    .rc-page-header { padding: 28px 32px 22px; display: flex; align-items: center; justify-content: space-between; gap: 16px; }
    .rc-eyebrow { color: #64748b; font-size: 11px; letter-spacing: 1.5px; font-weight: 700; }
    .rc-page-header h1 { margin: 6px 0; font-size: 28px; line-height: 1.2; font-weight: 750; letter-spacing: -.7px; color: #0f172a; }
    .rc-page-header p { font-size: 14px; color: #64748b; }
    .rc-app-badge { background: #eff6ff; color: #2563eb; border: 1px solid #dbeafe; border-radius: 9px; padding: 8px 16px; font-weight: 700; font-size: 13px; }
    .container .tabs-container { border-bottom: 1px solid #e2e8f0; }
    .container .tabs { padding: 0 32px; gap: 22px; }
    .container .tab-button { padding: 14px 2px; font-size: 14px; text-transform: none; letter-spacing: 0; font-weight: 600; border-bottom-width: 2px; }
    .container .tab-button::after { display: none; }
    .container .tab-button.active { background: transparent; }
    .container .tab-content { padding: 24px 32px; }
    .container .content-wrapper { padding: 0; }
    .container .search-form { padding: 0; border: none; background: transparent; margin: 0 0 24px; }
    .container #searchForm { display: flex; align-items: flex-end; gap: 12px; flex-wrap: wrap; }
    .container #searchForm .form-group { flex: 0 1 170px; min-width: 145px; margin: 0; }
    .container #searchForm .form-group:first-child { flex: 1 1 280px; max-width: 460px; }
    .container #searchForm label { font-size: 12px; font-weight: 600; text-transform: none; color: #475569; margin-bottom: 7px; }
    .container #searchForm input { width: 100%; height: 42px; font-size: 14px; border: 1px solid #cbd5e1; border-radius: 8px; padding: 10px 12px; background: #fff; color: #0f172a; }
    .container .btn { min-height: 42px; padding: 10px 16px; border-radius: 8px; font-size: 13px; text-transform: none; font-weight: 600; letter-spacing: 0; box-shadow: none; }
    .container .btn-primary { background: #2563eb; border: 1px solid #2563eb; color: #fff; }
    .container .btn-secondary { background: #fff; border: 1px solid #cbd5e1; color: #475569; }
    .container .btn:hover { transform: none; filter: brightness(.97); }
    .container .stats-bar { display: grid; grid-template-columns: repeat(4,minmax(0,1fr)); padding: 0; gap: 16px; background: transparent; margin: 0 0 18px; }
    .container .stat-item { padding: 20px; border: 1px solid #e2e8f0 !important; border-radius: 10px; box-shadow: none; background: white; min-height: 100px; }
    .container .stat-item.active-filter { outline: none; background: #eff6ff; border-color: #bfdbfe !important; }
    .container .stat-icon { display: none; }
    .container .stat-label { font-size: 12px; text-transform: none; letter-spacing: 0; margin-bottom: 8px; color: #64748b; }
    .container .stat-value { font-size: 26px; line-height: 1.2; letter-spacing: -.5px; }
    .container .stat-item.entradas .stat-value { color: #059669; }
    .container .stat-item.saidas .stat-value { color: #dc2626; }
    .container .stat-item.saldo .stat-value { color: #1d4ed8; }
    .container .rc-conference { margin: 0 0 24px; padding: 0; border: 1px solid #dbe5ec; border-radius: 10px; overflow: hidden; }
    .rc-status-line { padding: 14px 18px; display: flex; align-items: center; gap: 12px; flex-wrap: wrap; font-size: 12px; background: #f8fafc; }
    .rc-status-line strong { color: #0f172a; font-size: 13px; }
    .rc-status-dot { width: 8px; height: 8px; border-radius: 50%; background: #64748b; flex-shrink: 0; }
    .rc-scope { margin-left: auto; color: #64748b; }
    .rc-analysis > summary { list-style: none; padding: 12px 18px; color: #2563eb; font-size: 13px; border-top: 1px solid #e2e8f0; }
    .rc-analysis > summary::after { content: '+'; float: right; font-size: 17px; }
    .rc-analysis[open] > summary::after { content: '−'; }
    .rc-analysis > summary span { color: #64748b; font-weight: 400; margin-left: 12px; font-size: 12px; }
    .rc-analysis > p, .rc-analysis > details, .rc-analysis > div { margin: 12px 18px; }
    .rc-analysis p { color: #64748b; line-height: 1.6; }
    .rc-tools { display: flex; justify-content: space-between; align-items: center; gap: 16px; margin: 0 0 16px; flex-wrap: wrap; }
    .rc-tools h2 { font-size: 18px; font-weight: 700; color: #0f172a; margin: 0; }
    .rc-tools p { color: #64748b; font-size: 12px; margin: 4px 0 0; }
    .rc-tool-actions { display: flex; gap: 8px; }
    .rc-print-period { display: none; }
    .container .table-wrapper { border-radius: 10px; border-color: #e2e8f0; }
    .container thead { background: #f8fafc; box-shadow: none; }
    .container th { color: #64748b; padding: 14px 14px; font-size: 12px; text-shadow: none; letter-spacing: .4px; border-bottom: 1px solid #e2e8f0; white-space: nowrap; }
    .container td { padding: 14px 14px; font-size: 15px; color: #334155; }
    .container tbody tr.highlight { background: white; }
    .container tbody tr:nth-child(even) { background: #fcfdff; }
    .container tbody tr:hover { background: #f1f5f9; }
    .container td a { font-weight: 500; color: #2563eb; }
    .container .badge { border-radius: 5px; padding: 4px 7px; font-size: 14px; font-weight: 600; }
    .container .badge-info { background: #f1f5f9; color: #475569; }
    .container .badge-info img { display: none; }
    .container .finance-table td:nth-child(6), .container .finance-table td:nth-child(7) { text-align: right; font-variant-numeric: tabular-nums; }
    @media (max-width: 900px) { .container { width: calc(100% - 20px); margin: 12px auto; } .rc-page-header { padding: 22px 18px; } .rc-page-header h1 { font-size: 23px; } .container .tabs { padding: 0 18px; overflow-x: auto; gap: 18px; } .container .tab-content { padding: 20px 18px; } .container .stats-bar { grid-template-columns: repeat(2,minmax(0,1fr)); gap: 10px; } .container .stat-item { padding: 16px; } .container .stat-value { font-size: 22px; } .rc-analysis > summary span { display: none; } }
    @media print { .container { width: 100%; max-width: none; border: 0; margin: 0; box-shadow: none; } .rc-page-header { padding: 0 0 12px; } .rc-app-badge, .tabs-container { display: none; } .container .tab-content { padding: 0; } .rc-print-period { display: block; } .rc-status-line { padding: 8px; } .rc-analysis > summary::after { display: none; } .container .stat-item { padding: 8px; min-height: 0; } .container .stat-value { font-size: 17px; } .container th, .container td { font-size: 9px; padding: 4px; } }

    @media screen {
        #rc-caixa.container { width: calc(100% - 32px); max-width: none; }
        #rc-caixa .finance-table td { font-size: 15px; line-height: 1.5; }
        #rc-caixa #searchForm label { font-size: 13px; }
        #rc-caixa #searchForm input, #rc-caixa .btn { font-size: 15px; }
    }
    @media screen and (max-width: 900px) { #rc-caixa.container { width: calc(100% - 20px); } }
    .bt-heading { display: flex; justify-content: space-between; align-items: center; gap: 18px; margin-bottom: 22px; }
    .bt-heading h2 { font-size: 24px; font-weight: 700; color: #0f172a; }
    .bt-heading p, .bt-note { color: #64748b; font-size: 13px; margin: 8px 0 18px; }
    .bt-form { display: flex; flex-wrap: wrap; gap: 12px; align-items: end; margin: 18px 0; padding: 18px; border: 1px solid #e2e8f0; border-radius: 10px; background: #f8fafc; }
    .bt-form label { display: flex; flex-direction: column; gap: 7px; font-size: 12px; color: #475569; flex: 1 1 170px; }
    .bt-form input, .bt-form select { padding: 10px; height: 42px; border: 1px solid #cbd5e1; border-radius: 7px; background: white; font-size: 14px; min-width: 0; width: 100%; color: #0f172a; }
    .bt-warning { border: 1px solid #fde68a; border-radius: 10px; background: #fffbeb; color: #92400e; padding: 16px; margin: 18px 0; font-size: 14px; }
    .bt-warning p { margin-top: 6px; font-size: 13px; }
    .bt-wallets { margin: 16px 0; } .bt-wallets summary { cursor: pointer; padding: 12px 0; font-size: 14px; font-weight: 600; }
    .bt-table { min-width: 1150px; } .bt-table td { max-width: 330px; vertical-align: top; overflow-wrap: anywhere; } .bt-table small { color: #64748b; }
    .bt-table td p { margin: 0 0 10px; } .bt-pagination { display: flex; align-items: center; justify-content: end; gap: 12px; font-size: 13px; margin-top: 18px; }
    .container .tabs { flex-wrap: wrap; }
    .lo-multis { display: grid; grid-template-columns: repeat(3,minmax(0,1fr)); gap: 14px; width: 100%; }
    .lo-multis details, .lo-models, .lo-summaries details { border: 1px solid #e2e8f0; border-radius: 9px; background: #fff; padding: 14px; }
    .lo-multis summary, .lo-models summary, .lo-summaries summary { cursor: pointer; font-size: 14px; font-weight: 600; margin-bottom: 10px; }
    .lo-checks { max-height: 180px; overflow-y: auto; display: grid; gap: 7px; margin-bottom: 8px; }
    .container .lo-checks label { display: flex; flex-direction: row; align-items: center; gap: 8px; flex: initial; font-size: 13px; text-transform: none; }
    .container .lo-checks input { width: 17px; height: 17px; padding: 0; flex-shrink: 0; }
    .lo-actions { display: flex; gap: 9px; flex-wrap: wrap; }
    .lo-models { display: flex; gap: 12px; align-items: center; flex-wrap: wrap; margin: 18px 0; }
    .lo-models p, .lo-multis small { font-size: 12px; color: #64748b; }
    .lo-models select, .lo-models input:not([type=hidden]):not([type=checkbox]) { padding: 10px; border: 1px solid #cbd5e1; border-radius: 7px; font-size: 14px; }
    .lo-models label { display: block; margin: 9px 0; font-size: 13px; text-transform: none; }
    .lo-message { padding: 12px; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; font-size: 14px; }
    .lo-summaries { display: grid; grid-template-columns: repeat(2,minmax(0,1fr)); gap: 14px; margin: 18px 0; }
    .lo-table { min-width: 1200px; } .lo-table td { vertical-align: top; overflow-wrap: anywhere; max-width: 330px; }
    .lo-period { margin: 12px 0; font-size: 13px; color: #475569; }
    @media(max-width:900px) { .lo-multis, .lo-summaries { grid-template-columns: 1fr; } }
    @media print { body:has(#content-loja.active) #content-financeiro { display: none !important; } #content-loja .lo-form, #content-loja .lo-models, #content-loja .rc-tools button, #content-loja .bt-pagination { display: none !important; } .lo-table { min-width: 0; } .lo-summaries { display: block; } }

    /* Classic compact layout, in the datacenter blue palette. Scoped to the addon. */
    @media screen {
        #rc-caixa.container { --primary: #087bd3; --primary-dark: #075cae; --info: #198fe0; width: calc(100% - 24px); max-width: none; margin: 14px auto; border: 0; border-radius: 8px; box-shadow: 0 5px 18px #0c244322; }
        #rc-caixa .rc-page-header { min-height: 44px; padding: 10px 20px; background: linear-gradient(110deg, #155cc5 0%, #098ddb 65%, #26b7eb 100%); color: white; border-radius: 8px 8px 0 0; }
        #rc-caixa .rc-page-header > div { display: flex; align-items: center; gap: 15px; }
        #rc-caixa .rc-page-header .rc-eyebrow { color: #edf8ff; font-size: 11px; letter-spacing: .6px; }
        #rc-caixa .rc-page-header h1 { color: white; font-size: 15px; font-weight: 600; letter-spacing: 0; margin: 0; line-height: 1.4; }
        #rc-caixa .rc-page-header .rc-eyebrow::after { content: '/'; margin-left: 14px; opacity: .65; }
        #rc-caixa .rc-page-header .rc-app-badge { color: #fff; background: #ffffff18; border-color: #ffffff60; border-radius: 4px; padding: 3px 9px; font-size: 11px; }
        #rc-caixa .tabs { padding: 12px 16px 0; gap: 2px; flex-wrap: wrap; }
        #rc-caixa .tabs-container { border-bottom: 1px solid #dbe5f1; }
        #rc-caixa .tab-button { padding: 10px; font-size: 12px; text-transform: uppercase; letter-spacing: .2px; border-bottom: 2px solid transparent; gap: 6px; }
        #rc-caixa .tab-button:not(:last-child)::after { display: block; content: '|'; font-size: 12px; right: -3px; padding: 0; color: #c8d7e8; }
        #rc-caixa .tab-button.active { color: #087bd3; background: #f3f8ff; border-bottom-color: #138de1; }
        #rc-caixa .tab-button:hover { color: #087bd3; background: #f3f8ff; }
        #rc-caixa .tab-content { padding: 22px 20px; }
        #rc-caixa .content-wrapper { padding: 0; }
        #rc-caixa .search-form { padding: 12px 14px; background: #f6f8fb; border: 1px solid #dfe6ef; border-radius: 6px; margin: 5px 0 12px; }
        #rc-caixa #searchForm { justify-content: flex-start; gap: 9px; }
        #rc-caixa #searchForm .form-group { flex: 0 1 180px; min-width: 145px; }
        #rc-caixa #searchForm .form-group:first-child { flex: 0 1 250px; max-width: 300px; }
        #rc-caixa #searchForm label { font-size: 11px; margin-bottom: 5px; text-transform: uppercase; }
        #rc-caixa #searchForm input { height: 36px; font-size: 13px; padding: 7px 10px; border-color: #d7e1ed; border-radius: 4px; }
        #rc-caixa .btn { min-height: 34px; padding: 7px 12px; border-radius: 4px; font-size: 12px; font-weight: 600; }
        #rc-caixa .btn-primary { background: #087bd3; border-color: #087bd3; color: white; }
        #rc-caixa .btn-secondary { background: #fff; border-color: #1689d7; color: #086cb9; }
        #rc-caixa .btn-secondary:hover { background: #eaf5ff; }
        #rc-caixa .btn:disabled { opacity: .55; cursor: default; }
        #rc-caixa .stats-bar { display: grid; grid-template-columns: repeat(4,minmax(0,1fr)); gap: 12px; padding: 9px 11px; background: linear-gradient(110deg,#f5f7fa,#e9edf2); border-radius: 7px; margin: 12px 0; }
        #rc-caixa .stat-item { display: flex; align-items: center; gap: 9px; min-height: 62px; padding: 10px 12px; border: 0 !important; border-left: 3px solid #3f638c !important; border-radius: 5px; background: white; box-shadow: 0 2px 4px #0b294d0a; }
        #rc-caixa .stat-icon { display: inline-flex; font-size: 20px; opacity: .9; }
        #rc-caixa .stat-item.entradas { border-left-color: #1593e1 !important; }
        #rc-caixa .stat-item.saidas { border-left-color: #f05265 !important; }
        #rc-caixa .stat-item.saldo { border-left-color: #087bd3 !important; }
        #rc-caixa .stat-item.active-filter { outline: 1px solid #459cde; outline-offset: 0; background: #f1f8ff; box-shadow: none; }
        #rc-caixa .stat-label { font-size: 11px; text-transform: uppercase; letter-spacing: .2px; margin-bottom: 4px; color: #506781; }
        #rc-caixa .stat-value { font-size: 19px; line-height: 1.2; letter-spacing: 0; }
        #rc-caixa .stat-item.entradas .stat-value { color: #1386d3; }
        #rc-caixa .stat-item.saldo .stat-value { color: #086ab5; }
        #rc-caixa .stat-item.saidas .stat-value { color: #e53e54; }
        #rc-caixa .rc-conference { margin: 12px 0; border-color: #d9e6f3; border-radius: 5px; }
        #rc-caixa .rc-status-line { padding: 8px 12px; background: #f3f8fd; font-size: 11px; gap: 10px; }
        #rc-caixa .rc-status-line strong { font-size: 12px; }
        #rc-caixa .rc-analysis > summary { padding: 8px 12px; font-size: 12px; }
        #rc-caixa .rc-tools { margin: 12px 0 9px; }
        #rc-caixa .rc-tools h2 { font-size: 15px; }
        #rc-caixa .rc-tools p { font-size: 11px; margin-top: 3px; }
        #rc-caixa .table-wrapper { border-radius: 6px; border-color: #dbe4ef; }
        #rc-caixa thead { background: linear-gradient(110deg, #176bd0 0%, #1199e1 100%); }
        #rc-caixa th { padding: 11px 10px; color: #fff; font-size: 12px; font-weight: 700; letter-spacing: .35px; text-shadow: 0 1px 1px #06417c35; border-bottom: 1px solid #0c6fba; }
        #rc-caixa td, #rc-caixa .finance-table td { font-size: 13.5px; padding: 10px; line-height: 1.4; }
        #rc-caixa tbody tr:nth-child(even), #rc-caixa tbody tr.highlight { background: #f5f7fa; }
        #rc-caixa tbody tr:hover { background: #eaf4ff; }
        #rc-caixa td a { color: #0978c8; font-weight: 500; }
        #rc-caixa .badge { font-size: 12px; padding: 3px 6px; border-radius: 4px; }
        #rc-caixa .badge-success { background: #e0f3ff; color: #0869ab; }
        #rc-caixa .badge-info { background: #e4efff; color: #1262ad; }
        #rc-caixa .client-icon-clean { color: #688bad; }
        #rc-caixa .bt-heading { margin-bottom: 14px; gap: 12px; }
        #rc-caixa .bt-heading h2 { font-size: 20px; color: #17466f; }
        #rc-caixa .bt-heading p, #rc-caixa .bt-note { font-size: 12px; margin: 6px 0 12px; }
        #rc-caixa .bt-form { padding: 12px; margin: 12px 0; border-radius: 6px; background: #f6f8fb; }
        #rc-caixa .bt-form input, #rc-caixa .bt-form select { height: 36px; padding: 7px 9px; font-size: 13px; border-radius: 4px; }
        #rc-caixa .lo-checks input { width: 16px; height: 16px; }
        #rc-caixa .lo-multis details, #rc-caixa .lo-models, #rc-caixa .lo-summaries details { padding: 11px; border-radius: 5px; }
        #rc-caixa .lo-multis summary, #rc-caixa .lo-models summary, #rc-caixa .lo-summaries summary { font-size: 13px; color: #25547d; }
        #rc-caixa .lo-checks label { font-size: 12px; }
        #rc-caixa .lo-models { margin: 12px 0; }
        #rc-caixa .rc-app-badge { padding: 5px 9px; font-size: 11px; border-radius: 4px; }
    }
    @media screen and (max-width: 900px) {
        #rc-caixa.container { width: calc(100% - 16px); margin: 9px auto; }
        #rc-caixa .tab-content { padding: 16px 12px; }
        #rc-caixa .tabs { padding: 7px 7px 0; gap: 0; }
        #rc-caixa .tab-button { font-size: 11px; padding: 10px; }
        #rc-caixa .stats-bar { grid-template-columns: repeat(2,minmax(0,1fr)); gap: 8px; padding: 8px; }
        #rc-caixa .stat-item { min-height: 58px; padding: 8px; }
        #rc-caixa .stat-value { font-size: 17px; }
        #rc-caixa .rc-page-header { padding: 9px 12px; }
    }

</style>
<link href="responsive_layout.css?v=<?php echo rawurlencode($manifestVersion.'-'.filemtime(__DIR__.'/responsive_layout.css')); ?>" rel="stylesheet" type="text/css" />

</head>

<script>
    function rcOpenConference() {
        const details = document.getElementById('rc-full-conference');
        if (!details) return;
        details.open = true;
        details.querySelectorAll('details').forEach(function(item) { item.open = true; });
        document.getElementById('rc-conference-shortcut').setAttribute('aria-expanded', 'true');
        details.closest('.rc-conference').scrollIntoView({behavior: 'smooth', block: 'start'});
        details.querySelector('summary').focus({preventScroll: true});
    }
    function rcExport() {
        const table = document.querySelector('.finance-table');
        const rows = table ? Array.from(table.querySelectorAll('tbody tr')).filter(row => !row.classList.contains('hidden') && !row.classList.contains('filter-hidden')) : [];
        const cell = value => {
            value = String(value).trim();
            if (/^[=+@\-\t\r]/.test(value)) value = "'" + value;
            return '"' + value.replace(/"/g, '""') + '"';
        };
        const line = values => values.map(cell).join(';');
        const amount = value => Math.round(Number(value.replace(/[^0-9,-]/g, '').replace(',', '.')) * 100) || 0;
        const money = value => (value / 100).toFixed(2).replace('.', ',');
        let incoming = 0, outgoing = 0, extras = 0;
        const content = [line(['Conferência de caixa — linhas visíveis']), line([document.querySelector('.rc-print-period').textContent])];
        if (table) content.push(line(Array.from(table.querySelectorAll('thead th')).map(th => th.textContent)));
        rows.forEach(row => {
            const values = Array.from(row.cells).map(td => td.textContent.trim());
            values[0] = row.dataset.client || values[0];
            incoming += Number(row.dataset.incoming || 0); outgoing += Number(row.dataset.outgoing || 0) - (tariffsVisible ? 0 : Number(row.dataset.fee || 0)); if (!tariffsVisible && Number(row.dataset.fee || 0) > 0) values[7] = '--'; extras += Number(row.dataset.extra || 0); content.push(line(values));
        });
        content.push(line(['Linhas exportadas', rows.length]), line(['Total entradas', money(incoming)]), line(['Total saídas', money(outgoing)]), line(['Acréscimos identificados (já incluídos nas entradas)', money(extras)]), line(['Resultado das linhas exportadas', money(incoming - outgoing)]));
        const url = URL.createObjectURL(new Blob(['\ufeff' + content.join('\r\n')], {type: 'text/csv;charset=utf-8;'}));
        const a = document.createElement('a'); a.href = url; a.download = 'conferencia-caixa.csv'; a.click();
        setTimeout(() => URL.revokeObjectURL(url), 1000);
    }

    function clearSearch() {
        document.getElementById('search').value = '';
        document.getElementById('data_inicial').value = '<?php echo date('Y-m-d'); ?>';
        document.getElementById('data_final').value = '<?php echo date('Y-m-d'); ?>';
        window.location.href = window.location.pathname;
    }

    let movementFilter = 'todos';
    let tariffsVisible = true;

    function normalizeSearch(value) {
        return (value || '').toLocaleLowerCase('pt-BR').normalize('NFD').replace(/[\u0300-\u036f]/g, '');
    }

    function applyTableFilters() {
        const searchInput = document.getElementById('search');
        const term = normalizeSearch(searchInput ? searchInput.value.trim() : '');

        const feeRows = document.querySelectorAll('#financeTableBody tr.tarifa-row, #financeTableBody tr[data-fee]:not([data-fee="0"])');
        const feeButton = document.getElementById('toggleButton');
        if (feeButton) { feeButton.disabled = !feeRows.length; feeButton.textContent = !feeRows.length ? 'Sem tarifas no período' : (tariffsVisible ? 'Esconder taxas na tabela' : 'Exibir taxas na tabela'); }
        let footerIncoming = 0, footerOutgoing = 0, footerExtras = 0;
        document.querySelectorAll('#financeTableBody tr[data-search]').forEach(function(row) {
            const matchesText = !term || normalizeSearch(row.dataset.search).indexOf(term) !== -1;
            const movement = row.dataset.movement || 'outro';
            const matchesMovement = movementFilter === 'todos' || movement === movementFilter || (movementFilter === 'saida' && Number(row.dataset.outgoing || 0) > 0) || (movementFilter === 'acrescimos' && Number(row.dataset.extra || 0) > 0);
            const feeHidden = row.classList.contains('tarifa-row') && !tariffsVisible;
            row.classList.toggle('hidden', feeHidden);
            row.querySelectorAll('.rc-grouped-fee').forEach(function(value) { value.classList.toggle('hidden', !tariffsVisible); });
            row.classList.toggle('filter-hidden', !(matchesText && matchesMovement));
            if (matchesText && matchesMovement && !feeHidden) {
                footerIncoming += Number(row.dataset.incoming || 0);
                footerOutgoing += Number(row.dataset.outgoing || 0) - (tariffsVisible ? 0 : Number(row.dataset.fee || 0));
                footerExtras += Number(row.dataset.extra || 0);
            }
        });
        const footerMoney = value => 'R$ ' + (value / 100).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        [['incoming', footerIncoming], ['extra', footerExtras], ['outgoing', footerOutgoing], ['net', footerIncoming - footerOutgoing]].forEach(function(item) { const cell = document.getElementById('rc-footer-' + item[0]); if (cell) cell.textContent = footerMoney(item[1]); });
        // Os cards preservam os totais calculados no servidor para o período.
        // Filtros de visualização afetam somente as linhas da tabela.
    }

    function setMovementFilter(filter, card) {
        movementFilter = filter;
        if (filter === 'saida') {
            tariffsVisible = true;
            document.querySelectorAll('tr.tarifa-row').forEach(function(row) {
                row.classList.remove('hidden');
            });
            const toggleButton = document.getElementById('toggleButton');
            if (toggleButton) toggleButton.innerText = 'Esconder taxas na tabela';
        }
        document.querySelectorAll('.stat-item[data-filter]').forEach(function(item) {
            item.classList.toggle('active-filter', item === card);
            item.setAttribute('aria-pressed', item === card ? 'true' : 'false');
        });
        applyTableFilters();
    }

    window.rcPrintFilterState = function () { return {movement: movementFilter, fees: tariffsVisible}; };
    window.addEventListener('beforeprint', function() { document.querySelectorAll('.rc-conference details').forEach(function(d) { d.dataset.rcOpen = d.open ? '1' : '0'; d.open = true; }); });
    window.addEventListener('afterprint', function() { document.querySelectorAll('.rc-conference details').forEach(function(d) { d.open = d.dataset.rcOpen === '1'; }); });

    window.onload = function() {
        applyTableFilters();

        const searchInput = document.getElementById('search');
        if (searchInput) {
            searchInput.addEventListener('input', applyTableFilters);
        }

        document.querySelectorAll('.stat-item[data-filter]').forEach(function(card) {
            card.addEventListener('click', function() {
                setMovementFilter(card.dataset.filter, card);
            });
            card.addEventListener('keydown', function(event) {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    setMovementFilter(card.dataset.filter, card);
                }
            });
        });
        applyTableFilters();
        
        // Verificar se deve abrir uma aba específica
        const urlParams = new URLSearchParams(window.location.search);
        const activeTab = urlParams.get('tab');
        
        if (activeTab === 'loja' || activeTab === 'carteiras' || activeTab === 'problemas') {
            switchTab(activeTab);
        } else if (activeTab === 'graficos') {
            switchTab('graficos');
        } else if (activeTab === 'ticket') {
            switchTab('ticket');
        } else if (activeTab === 'evolucao') {
            switchTab('evolucao');
        } else if (activeTab === 'anomalias') {
            switchTab('anomalias');
        }
    };

    function toggleTarifaRows() {
        tariffsVisible = !tariffsVisible;
        applyTableFilters();
    }

    function switchTab(tabName) {
        if (tabName === 'loja' || tabName === 'carteiras' || tabName === 'problemas') {
            const targetUrl = new URL(window.location.href);
            if (targetUrl.searchParams.get('tab') !== tabName) {
                targetUrl.search = ''; targetUrl.searchParams.set('tab', tabName);
                window.location.href = targetUrl.toString(); return;
            }
        }

        // Remove active class from all tabs and contents
        document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
        
        // Add active class to selected tab and content
        document.getElementById('tab-' + tabName).classList.add('active');
        document.getElementById('content-' + tabName).classList.add('active');
        const mobileTab = document.getElementById('rc-mobile-tab');
        if (mobileTab) mobileTab.value = tabName;
        
        // Atualizar URL com a aba ativa (sem recarregar a página)
        const currentUrl = new URL(window.location.href);
        if (tabName === 'loja' || tabName === 'carteiras' || tabName === 'problemas' || tabName === 'graficos' || tabName === 'ticket' || tabName === 'evolucao' || tabName === 'anomalias') {
            currentUrl.searchParams.set('tab', tabName);
        } else {
            // Remove o parâmetro tab quando voltar para financeiro
            currentUrl.searchParams.delete('tab');
        }
        window.history.pushState({}, '', currentUrl);
        
        // Initialize charts based on active tab
        if (tabName === 'graficos' && typeof initCharts === 'function') {
            setTimeout(initCharts, 100);
        } else if (tabName === 'ticket' && typeof initTicketChart === 'function') {
            setTimeout(initTicketChart, 100);
        } else if (tabName === 'evolucao' && typeof initEvolucaoCharts === 'function') {
            setTimeout(initEvolucaoCharts, 100);
        }
    }
</script>

<body>
    <div id="rc-native-top"><?php include('../../topo.php'); ?></div>
    <script src="navbar_layout.js"></script>
    <script defer src="payment_detail.js?v=<?php echo rawurlencode($manifestVersion); ?>"></script>
    <script defer src="mobile_tables.js?v=<?php echo rawurlencode($manifestVersion); ?>"></script>
    <script src="relatorio_print.js?v=<?php echo rawurlencode($manifestVersion.'-'.filemtime(__DIR__.'/relatorio_print.js')); ?>"></script>

    <div class="container" id="rc-caixa">
        <header class="rc-page-header"><div><span class="rc-eyebrow">ADDON</span><h1>Relatório de Caixa</h1></div><span class="rc-app-badge">v<?php echo htmlspecialchars($manifestVersion, ENT_QUOTES, 'UTF-8'); ?></span></header>

        <!-- TABS -->
        <div class="tabs-container">
            <label class="rc-mobile-navigation" for="rc-mobile-tab">Menu do caixa
                <select id="rc-mobile-tab" onchange="switchTab(this.value)">
                    <option value="financeiro">Movimentações</option><option value="graficos">Gráficos</option><option value="ticket">Ticket médio</option><option value="evolucao">Evolução</option><option value="loja">Caixa personalizado</option><option value="carteiras">Títulos por banco</option><option value="problemas">Problemas nos boletos</option><option value="anomalias">Anomalias</option>
                </select>
            </label>
            <div class="tabs">
                <button class="tab-button active" id="tab-financeiro" onclick="switchTab('financeiro')">
                    <span aria-hidden="true">📋</span> Movimentações
                </button>
                <button class="tab-button" id="tab-graficos" onclick="switchTab('graficos')">
                    <span aria-hidden="true">📊</span> Gráficos
                </button>
                <button class="tab-button" id="tab-ticket" onclick="switchTab('ticket')">
                    <span aria-hidden="true">🎫</span> Ticket médio
                </button>
                <button class="tab-button" id="tab-evolucao" onclick="switchTab('evolucao')">
                    <span aria-hidden="true">📈</span> Evolução
                </button>
                <button class="tab-button" id="tab-loja" onclick="switchTab('loja')"><span aria-hidden="true">🏪</span> CAIXA PERSONALIZADO</button>
                <button class="tab-button" id="tab-carteiras" onclick="switchTab('carteiras')"><span aria-hidden="true">🏦</span> Títulos por banco</button>
                <button class="tab-button" id="tab-problemas" onclick="switchTab('problemas')"><span aria-hidden="true">⚠️</span> Problemas nos boletos</button>
                <button class="tab-button" id="tab-anomalias" onclick="switchTab('anomalias')">
                    <span aria-hidden="true">🔎</span> Anomalias
                </button>
            </div>
        </div>

        <?php include('config.php'); ?>

        <?php if ($acesso_permitido) : ?>
        <?php require_once __DIR__.'/provedor_report.php'; ?>
        <script type="application/json" id="rc-provider-data"><?php echo json_encode(rc_report_provider($link),JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_INVALID_UTF8_SUBSTITUTE); ?></script>

            <!-- TAB CONTENT: FINANCEIRO -->
            <div id="content-financeiro" class="tab-content active">
                <div class="content-wrapper">
                    <div class="rc-shortcuts" aria-label="Acessos rápidos do caixa">
                        <?php foreach ($rc_quick_periods as $rc_shortcut): ?>
                        <a class="rc-shortcut-card" href="?<?php echo htmlspecialchars(http_build_query(array('tab'=>'financeiro','search'=>'','data_inicial'=>$rc_shortcut['start'],'data_final'=>$rc_shortcut['end'])),ENT_QUOTES,'UTF-8'); ?>" title="Abrir o período e limpar todos os filtros"><strong><?php echo htmlspecialchars($rc_shortcut['label'],ENT_QUOTES,'UTF-8'); ?></strong><span><?php echo htmlspecialchars($rc_shortcut['display'],ENT_QUOTES,'UTF-8'); ?></span></a>
                        <?php endforeach; ?>
                        <button type="button" class="rc-shortcut-card" id="rc-conference-shortcut" aria-controls="rc-full-conference" aria-expanded="false" onclick="rcOpenConference()"><strong>CONFERÊNCIA COMPLETA</strong><span>Resumos e alertas de todos os clientes do período</span></button>
                    </div>
                    <div class="search-form">
                        <form id="searchForm" method="GET">
                            <div class="form-group">
                                <label for="search">Cliente ou login</label>
                                <input type="text" id="search" name="search" placeholder="Pesquisar cliente ou login…" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" autocomplete="off">
                            </div>
                            
                            <div class="form-group">
                                <label for="data_inicial">De</label>
                                <input type="date" id="data_inicial" name="data_inicial" value="<?php echo isset($_GET['data_inicial']) ? htmlspecialchars($_GET['data_inicial']) : date('Y-m-d'); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="data_final">Até</label>
                                <input type="date" id="data_final" name="data_final" value="<?php echo isset($_GET['data_final']) ? htmlspecialchars($_GET['data_final']) : date('Y-m-d'); ?>">
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Aplicar período</button>
                            <button type="button" onclick="clearSearch()" class="btn btn-secondary">Limpar</button>
                            <button type="button" id="toggleButton" onclick="toggleTarifaRows()" class="btn btn-secondary" aria-describedby="rc-fees-help" title="Mostra ou esconde taxas de boleto na tabela. Os cards mantêm o caixa completo.">Esconder taxas na tabela</button>

                        </form>
                        <p id="rc-fees-help" class="bt-note" style="margin:8px 0 0">Taxas de boleto: o botão altera apenas a tabela, seus totais de baixo, CSV e impressão. Os cards do período sempre incluem as taxas.</p>
                    </div>

                    <?php
                    // Dados de conexão com o banco de dados já estão em config.php
                    $query = "SELECT c.entrada, c.saida, c.historico, c.data, c.usuario 
                              FROM sis_caixa c ";

                    if (!isset($_GET['data_inicial']) && !isset($_GET['data_final'])) {
                        $data_atual = date('Y-m-d');
                        $query .= " WHERE DATE(c.data) = '$data_atual'";
                    }
                    else if (isset($_GET['data_inicial']) && isset($_GET['data_final'])) {
                        $data_inicial = date('Y-m-d', strtotime($_GET['data_inicial']));
                        $data_final = date('Y-m-d', strtotime($_GET['data_final']));
                        $data_final .= ' 23:59:59';
                        $query .= " WHERE DATE(c.data) BETWEEN '$data_inicial' AND '$data_final'";
                    }

                    if (isset($_GET['search'])) {
                        $search_term = mysqli_real_escape_string($link, trim($_GET['search']));
                        $query .= " AND (c.historico LIKE '%$search_term%' OR c.usuario LIKE '%$search_term%' 
                        OR EXISTS (SELECT 1 FROM sis_lanc sl
                                       LEFT JOIN sis_cliente sc ON sc.login = sl.login
                                       WHERE LOWER(c.historico) REGEXP 't[ií]tulo([^0-9]{0,30})[0-9]+' AND 
                                             CAST(SUBSTRING_INDEX(TRIM(TRIM(LEADING ':' FROM TRIM(SUBSTRING_INDEX(LOWER(REPLACE(c.historico, 'título', 'titulo')), 'titulo', -1)))), ' ', 1) AS UNSIGNED) = sl.id AND 
                                             (sl.login LIKE '%$search_term%' OR sc.nome LIKE '%$search_term%')))";
                    }

                    $query .= " ORDER BY c.data DESC";
                    $result = mysqli_query($link, $query);

                    $rc_cash_rows = array();
                    while ($rc_row = mysqli_fetch_assoc($result)) $rc_cash_rows[] = $rc_row;
                    $rc_extras = rc_extra_load($link, $rc_cash_rows);
                    $rc_display_rows = rc_cash_display($rc_cash_rows, $rc_extras);
                    mysqli_data_seek($result, 0);
                    $tot_entrada = 0;
                    $total_boletos_ = 0;
                    $tot_saida = 0;

                    while ($row = mysqli_fetch_assoc($result)) {
                        $tot_entrada += $row['entrada'];
                        $tot_saida += $row['saida'];
                        if ($row['entrada'] > 0) {
                            $total_boletos_++;
                        }
                    }

                    $saldo = $tot_entrada - $tot_saida;
                    ?>

                    <div class="stats-bar rc-cash-stats">
                        <div class="stat-item boletos" data-filter="entrada" role="button" tabindex="0" aria-pressed="false" title="Mostrar somente recebimentos">
                            <div class="stat-icon">📊</div>
                            <div class="stat-content">
                                <div class="stat-label">Recebimentos</div>
                                <div class="stat-value"><?php echo $total_boletos_; ?></div>
                            </div>
                        </div>
                        
                        <div class="stat-item entradas" data-filter="entrada" role="button" tabindex="0" aria-pressed="false" title="Mostrar somente entradas">
                            <div class="stat-icon">💰</div>
                            <div class="stat-content">
                                <div class="stat-label">Entradas</div>
                                <div class="stat-value">R$ <?php echo number_format($tot_entrada, 2, ',', '.'); ?></div>
                            </div>
                        </div>
                        
                        <div class="stat-item saidas" data-filter="saida" role="button" tabindex="0" aria-pressed="false" title="Mostrar somente saídas">
                            <div class="stat-icon">💸</div>
                            <div class="stat-content">
                                <div class="stat-label">Saídas</div>
                                <div class="stat-value">R$ <?php echo number_format($tot_saida, 2, ',', '.'); ?></div>
                            </div>
                        </div>
                        
                        <div class="stat-item saldo active-filter" data-filter="todos" role="button" tabindex="0" aria-pressed="true" title="Mostrar todos os lançamentos">
                            <div class="stat-icon">✅</div>
                            <div class="stat-content">
                                <div class="stat-label">Resultado do período</div>
                                <div class="stat-value">R$ <?php echo number_format($saldo, 2, ',', '.'); ?></div>
                            </div>
                        </div>
                        <div class="stat-item rc-extras-card" data-filter="acrescimos" role="button" tabindex="0" aria-pressed="false" title="Mostrar somente recebimentos com acréscimos pagos. Já incluídos nas entradas."><div class="stat-content"><div class="stat-label">Acréscimos pagos</div><div class="stat-value"><?php echo bt_money($rc_extras['total']); ?></div></div></div>
                    </div>

<p class="bt-note">Acréscimos pagos = diferença positiva entre o valor pago e o valor original do título (juros e multa juntos), já incluída nas entradas. “Não identificado” indica recebimento sem vínculo único compatível com o pagamento do título. <?php echo (int)$rc_extras['unknown']; ?> recebimento(s) não identificado(s). A classificação usa os valores atuais do título.</p>
<p class="bt-note">Os cards mostram o caixa completo do período, incluindo todas as saídas e tarifas. Clique nos cards para filtrar somente a tabela; clique no resultado para ver tudo. CSV e relatórios de impressão acompanham os filtros da tabela e a escolha de exibir taxas.</p>
                    <?php include('conferencia_content.php'); ?>
                    <div class="rc-tools"><div><h2>Lançamentos</h2><p>Recebimento e taxa do boleto aparecem juntos quando há vínculo único de título, data e usuário.</p></div><div class="rc-tool-actions"><button type="button" class="btn btn-primary" onclick="rcPrintChoose('financeiro','download')">Baixar PDF</button> <button type="button" class="btn btn-secondary" onclick="rcExport()">Exportar CSV</button> <button type="button" class="btn btn-secondary" onclick="rcPrintChoose('financeiro')">Imprimir</button></div></div>
                    <p class="rc-print-period">Período: <?php echo rc_escape($rc_label); ?> · Gerado em <?php echo date('d/m/Y H:i'); ?> · Totais dos cards: consulta enviada ao servidor.</p>
                    <?php if ($result && mysqli_num_rows($result) > 0) : ?>
                        <div class="table-wrapper">
                            <table class="finance-table" data-period-start="<?php echo bt_h($rc_start->format('Y-m-d')); ?>" data-period-end="<?php echo bt_h($rc_end->format('Y-m-d')); ?>">
                                <thead>
                                    <tr>
                                        <th>Cliente</th>
                                        <th>Login</th>
                                        <th>Movimentação</th>
                                        <th>Usuário</th>
                                        <th>Histórico</th>
                                        <th>Entrada</th>
                                        <th>Acréscimos pagos</th>
                                        <th>Saída</th>
                                        <th>Título</th>
                                        <th>Vencimento</th>
                                    </tr>
                                </thead>
                                <tbody id="financeTableBody">
                                    <?php mysqli_data_seek($result, 0); ?>
                                    <?php $rowNumber = 0; ?>
                                    <?php foreach ($rc_display_rows as $rowNumber => $row) : ?>
                                        <?php
                                        $nomeClienteClass = ($rowNumber % 2 == 0) ? 'highlight' : '';
                                        $tarifaRowClass = lj_fee($row) ? 'tarifa-row' : '';
                                        $matches = array();
                                        preg_match('/t[ií]tulo(?:[^0-9]{0,30})(\d+)/iu', $row['historico'], $matches);
                                        $id = isset($matches[1]) ? $matches[1] : '--';
                                        $cliente_query = "SELECT c.nome, l.login, c.uuid_cliente, l.formapag, l.numconta, l.valor, l.valorpag, l.status, l.datapag, l.datavenc, b.nome AS conta_nome FROM sis_lanc l
                                            JOIN sis_cliente c ON l.login = c.login
                                            LEFT JOIN sis_boleto b ON b.id = l.numconta
                                            WHERE l.id = '$id'";
                                        $cliente_result = mysqli_query($link, $cliente_query);
                                        $cliente_row = $cliente_result ? mysqli_fetch_assoc($cliente_result) : null;
                                        $nome_cliente = isset($cliente_row['nome']) ? $cliente_row['nome'] : '--';
                                        $login = isset($cliente_row['login']) ? $cliente_row['login'] : '--';
                                        $uuid_cliente = isset($cliente_row['uuid_cliente']) ? $cliente_row['uuid_cliente'] : '';
                                        $movementType = ($row['entrada'] > 0) ? 'entrada' : (($row['saida'] > 0) ? 'saida' : 'outro');
                                        $rowSearch = $nome_cliente . ' ' . $login . ' ' . $row['historico'] . ' ' . $row['usuario'] . ' ' . $row['_fee_history'];
                                        ?>
                                        <tr data-client="<?php echo bt_h($nome_cliente); ?>" data-account="<?php echo bt_h(!empty($cliente_row['conta_nome']) ? $cliente_row['conta_nome'].' (#'.$cliente_row['numconta'].')' : 'Sem conta identificada'); ?>" data-method="<?php echo bt_h(!empty($cliente_row['formapag']) ? $cliente_row['formapag'] : 'Não informado'); ?>" data-fee="<?php echo (int)$row['_fee_amount']; ?>" data-extra="<?php echo (int)$row['_extra']; ?>" data-incoming="<?php echo (int)round($row['entrada'] * 100); ?>" data-outgoing="<?php echo (int)round($row['saida'] * 100); ?>" class="<?php echo $nomeClienteClass . ' ' . $tarifaRowClass; ?>" data-movement="<?php echo $movementType; ?>" data-search="<?php echo htmlspecialchars($rowSearch, ENT_QUOTES, 'UTF-8'); ?>">
                                            <td>
                                                <?php

                                                echo '<a href="../../cliente_det.hhvm?uuid=' . htmlspecialchars($uuid_cliente, ENT_QUOTES, 'UTF-8') . '" target="_blank" title="' . htmlspecialchars($nome_cliente, ENT_QUOTES, 'UTF-8') . '">';
                                                
                                                echo '<span class="rc-client-name">'.htmlspecialchars($nome_cliente, ENT_QUOTES, 'UTF-8').'</span>';
                                                echo '</a>';
                                                $clientFilter = '?' . http_build_query(array('search' => $login, 'data_inicial' => $rc_start->format('Y-m-d'), 'data_final' => $rc_end->format('Y-m-d')));
                                                if ($login !== '') echo '<a class="rc-client-login" href="'.bt_h($clientFilter).'" title="Filtrar por este login">'.bt_h($login).'</a>';
                                                ?>
                                            </td>

                                            <td>
                                                <?php
                                                $data_inicial = (!empty($_GET['data_inicial'])) ? date('Y-m-d', strtotime($_GET['data_inicial'])) : date('Y-m-d');
                                                $data_final = (!empty($_GET['data_final'])) ? date('Y-m-d', strtotime($_GET['data_final'])) : date('Y-m-d');
                                                $link_busca = "?search=" . urlencode($login) . "&data_inicial=" . urlencode($data_inicial) . "&data_final=" . urlencode($data_final);
                                                ?>
                                                <a href="<?php echo $link_busca; ?>" title="<?php echo htmlspecialchars($login); ?>"><?php echo htmlspecialchars($login, ENT_QUOTES, 'UTF-8'); ?></a>
                                            </td>

                                            <td style="font-weight: 500;"><?php echo date('d-m-Y H:i:s', strtotime($row['data'])); ?></td>
                                            <td style="font-weight: 500;"><?php echo $row['usuario']; ?></td>

                                            <td>
                                                <?php
                                                $historico = $row['historico'];
                                                ?>
                                                <span style="color: #059669; font-weight: 500;" title="<?php echo htmlspecialchars($historico); ?>">
                                                    <?php echo htmlspecialchars($historico); ?>
                                                </span>
                                            </td>

                                            <td>
                                                <?php if ($row['entrada'] > 0): ?>
                                                    <span class="badge badge-success">R$ <?php echo number_format($row['entrada'], 2, ',', '.'); ?></span>
                                                <?php else: ?>
                                                    --
                                                <?php endif; ?>
                                            </td>

                                            <td><?php if ($row['entrada'] > 0): ?><?php echo $row['_extra'] === null ? '<span title="Sem vínculo único compatível com o pagamento do título">Não identificado</span>' : ($row['_extra'] > 0 ? '<span class="rc-extra-paid" title="Acréscimos pagos do título — juros e multa, já incluídos na entrada">'.bt_money($row['_extra']).'</span>' : bt_money(0)); ?><?php else: ?>--<?php endif; ?></td>

                                            <td>
                                                <?php if ($row['saida'] > 0): ?>
                                                    <span class="badge badge-danger <?php echo $row['_fee_amount'] > 0 ? 'rc-grouped-fee' : ''; ?>" title="<?php echo htmlspecialchars(($row['_fee_amount'] > 0 || lj_fee($row)) ? 'Taxa do boleto — '.($row['_fee_history'] ?: $row['historico']) : $row['historico'], ENT_QUOTES, 'UTF-8'); ?>">R$ <?php echo number_format($row['saida'], 2, ',', '.'); ?></span>
                                                <?php else: ?>
                                                    --
                                                <?php endif; ?>
                                            </td>

                                            <td>
                                                <?php if ($cliente_row && (int)$id > 0):
                                                    $paymentDetail = array('title'=>(int)$id, 'client'=>$nome_cliente, 'login'=>$login, 'status'=>$cliente_row['status'], 'original'=>bt_cents($cliente_row['valor']), 'paid'=>bt_cents($cliente_row['valorpag']), 'due'=>$cliente_row['datavenc'], 'paidDate'=>$cliente_row['datapag'], 'method'=>$cliente_row['formapag'], 'account'=>!empty($cliente_row['conta_nome'])?$cliente_row['conta_nome'].' (#'.$cliente_row['numconta'].')':'Não identificada', 'movement'=>$row['data'], 'user'=>$row['usuario'], 'incoming'=>bt_cents($row['entrada']), 'outgoing'=>bt_cents($row['saida']), 'extra'=>$row['_extra'], 'fee'=>(int)$row['_fee_amount'], 'history'=>$row['historico']);
                                                ?>
                                                <button type="button" class="rc-title-detail" aria-label="Ver pagamento do título <?php echo (int)$id; ?>" data-payment="<?php echo bt_h(json_encode($paymentDetail, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE)); ?>"><?php echo (int)$id; ?></button>
                                                <?php else: ?><span><?php echo bt_h($id); ?></span><?php endif; ?>
                                            </td>

                                            <td style="font-weight: 500;">
                                                <?php
                                                $id = isset($matches[1]) ? $matches[1] : '';
                                                $boleto_query = "SELECT datavenc FROM sis_lanc WHERE id = '$id'";
                                                $boleto_result = mysqli_query($link, $boleto_query);
                                                $datavenc = mysqli_fetch_assoc($boleto_result)['datavenc'];
                                                if ($datavenc && $datavenc != '0000-00-00') {
                                                    echo date('d-m-Y', strtotime($datavenc));
                                                } else {
                                                    echo '<span style="color: #9CA3AF;">--</span>';
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                </tbody>
                                <tfoot><tr><th colspan="5" scope="row">TOTAL</th><td id="rc-footer-incoming"><?php echo bt_money((int)round($tot_entrada * 100)); ?></td><td id="rc-footer-extra" class="rc-extra-total" title="Acréscimos já incluídos nas entradas"><?php echo bt_money($rc_extras['total']); ?></td><td id="rc-footer-outgoing"><?php echo bt_money((int)round($tot_saida * 100)); ?></td><td colspan="2"></td></tr><tr class="rc-net-row"><th colspan="7" scope="row">SUBTOTAL DO CAIXA</th><td colspan="3" id="rc-footer-net"><?php echo bt_money((int)round(($tot_entrada - $tot_saida) * 100)); ?></td></tr></tfoot>
                            </table>
                        </div>

                    <?php else : ?>
                        <p class="no-data">Nenhum resultado encontrado.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div id="content-loja" class="tab-content"><div class="content-wrapper">
                <?php if (isset($_GET['tab']) && $_GET['tab'] === 'loja') include('loja_content.php'); ?>
            </div></div>
            <div id="content-carteiras" class="tab-content"><div class="content-wrapper">
                <?php if (isset($_GET['tab']) && $_GET['tab'] === 'carteiras') include('titulos_content.php'); ?>
            </div></div>
            <div id="content-problemas" class="tab-content"><div class="content-wrapper">
                <?php if (isset($_GET['tab']) && $_GET['tab'] === 'problemas') include('titulos_content.php'); ?>
            </div></div>
            <!-- TAB CONTENT: GRÁFICOS -->
            <div id="content-graficos" class="tab-content">
                <div class="content-wrapper">
                    <?php 
                    if (file_exists('graficos_content.php')) {
                        include('graficos_content.php'); 
                    } else {
                        echo '<p class="no-data">Arquivo graficos_content.php não encontrado.</p>';
                    }
                    ?>
                </div>
            </div>

            <!-- TAB CONTENT: TICKET -->
            <div id="content-ticket" class="tab-content">
                <div class="content-wrapper">
                    <?php 
                    if (file_exists('ticket_content.php')) {
                        include('ticket_content.php'); 
                    } else {
                        echo '<p class="no-data">Arquivo ticket_content.php não encontrado.</p>';
                    }
                    ?>
                </div>
            </div>

            <!-- TAB CONTENT: EVOLUÇÃO -->
            <div id="content-evolucao" class="tab-content">
                <div class="content-wrapper">
                    <?php 
                    if (file_exists('evolucao_content.php')) {
                        include('evolucao_content.php'); 
                    } else {
                        echo '<p class="no-data">Arquivo evolucao_content.php não encontrado.</p>';
                    }
                    ?>
                </div>
            </div>

            <!-- TAB CONTENT: ANOMALIAS -->
            <div id="content-anomalias" class="tab-content">
                <div class="content-wrapper">
                    <?php 
                    if (file_exists('anomalias_content.php')) {
                        include('anomalias_content.php'); 
                    } else {
                        echo '<p class="no-data">Arquivo anomalias_content.php não encontrado.</p>';
                    }
                    ?>
                </div>
            </div>

        <?php else : ?>
            <p class="no-data">🚫 Acesso não permitido!</p>
        <?php endif; ?>

        <?php include('../../baixo.php'); ?>
    </div>

    <script src="../../menu.js.php"></script>
    <?php include('../../rodape.php'); ?>

</body>
</html>

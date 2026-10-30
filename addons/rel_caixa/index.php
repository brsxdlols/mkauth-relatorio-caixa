<?php
// INCLUE FUNCOES DE ADDONS -----------------------------------------------------------------------
include('addons.class.php');

// VERIFICA SE O USUARIO ESTA LOGADO --------------------------------------------------------------
session_name('mka');
if (!isset($_SESSION)) session_start();
if (!isset($_SESSION['mka_logado']) && !isset($_SESSION['MKA_Logado'])) exit('Acesso negado... <a href="/admin/login.php">Fazer Login</a>');
// VERIFICA SE O USUARIO ESTA LOGADO --------------------------------------------------------------

$manifestTitle = $Manifest->{'name'} ?? '';
$manifestVersion = $Manifest->{'version'} ?? '';
?>

<!DOCTYPE html>
<html lang="pt-BR" class="has-navbar-fixed-top">
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

<style>
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
    .container { --primary: #2563eb; --primary-dark: #1d4ed8; --success: #059669; width: calc(100% - 48px); max-width: 1800px; margin: 24px auto; border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: 0 8px 32px #0f172a08; color: #334155; background: #fff; }
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
    .container th { color: #64748b; padding: 13px 14px; font-size: 11px; text-shadow: none; letter-spacing: .4px; border-bottom: 1px solid #e2e8f0; white-space: nowrap; }
    .container td { padding: 13px 14px; font-size: 13px; color: #334155; }
    .container tbody tr.highlight { background: white; }
    .container tbody tr:nth-child(even) { background: #fcfdff; }
    .container tbody tr:hover { background: #f1f5f9; }
    .container td a { font-weight: 500; color: #2563eb; }
    .container .badge { border-radius: 5px; padding: 4px 7px; font-size: 12px; font-weight: 600; }
    .container .badge-info { background: #f1f5f9; color: #475569; }
    .container .badge-info img { display: none; }
    .container .finance-table td:nth-child(6), .container .finance-table td:nth-child(7) { text-align: right; font-variant-numeric: tabular-nums; }
    @media (max-width: 900px) { .container { width: calc(100% - 20px); margin: 12px auto; } .rc-page-header { padding: 22px 18px; } .rc-page-header h1 { font-size: 23px; } .container .tabs { padding: 0 18px; overflow-x: auto; gap: 18px; } .container .tab-content { padding: 20px 18px; } .container .stats-bar { grid-template-columns: repeat(2,minmax(0,1fr)); gap: 10px; } .container .stat-item { padding: 16px; } .container .stat-value { font-size: 22px; } .rc-analysis > summary span { display: none; } }
    @media print { .container { width: 100%; max-width: none; border: 0; margin: 0; box-shadow: none; } .rc-page-header { padding: 0 0 12px; } .rc-app-badge, .tabs-container { display: none; } .container .tab-content { padding: 0; } .rc-print-period { display: block; } .rc-status-line { padding: 8px; } .rc-analysis > summary::after { display: none; } .container .stat-item { padding: 8px; min-height: 0; } .container .stat-value { font-size: 17px; } .container th, .container td { font-size: 9px; padding: 4px; } }

</style>

</head>

<script>
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
        let incoming = 0, outgoing = 0;
        const content = [line(['Conferência de caixa — linhas visíveis']), line([document.querySelector('.rc-print-period').textContent])];
        if (table) content.push(line(Array.from(table.querySelectorAll('thead th')).map(th => th.textContent)));
        rows.forEach(row => {
            const values = Array.from(row.cells).map(td => td.textContent.trim());
            incoming += amount(values[5]); outgoing += amount(values[6]); content.push(line(values));
        });
        content.push(line(['Linhas exportadas', rows.length]), line(['Total entradas', money(incoming)]), line(['Total saídas', money(outgoing)]), line(['Resultado das linhas exportadas', money(incoming - outgoing)]));
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

    function normalizeSearch(value) {
        return (value || '').toLocaleLowerCase('pt-BR').normalize('NFD').replace(/[\u0300-\u036f]/g, '');
    }

    function applyTableFilters() {
        const searchInput = document.getElementById('search');
        const term = normalizeSearch(searchInput ? searchInput.value.trim() : '');

        document.querySelectorAll('#financeTableBody tr[data-search]').forEach(function(row) {
            const matchesText = !term || normalizeSearch(row.dataset.search).indexOf(term) !== -1;
            const movement = row.dataset.movement || 'outro';
            const matchesMovement = movementFilter === 'todos' || movement === movementFilter;
            row.classList.toggle('filter-hidden', !(matchesText && matchesMovement));
        });
    }

    function setMovementFilter(filter, card) {
        movementFilter = filter;
        if (filter === 'saida') {
            document.querySelectorAll('tr.tarifa-row').forEach(function(row) {
                row.classList.remove('hidden');
            });
            const toggleButton = document.getElementById('toggleButton');
            if (toggleButton) toggleButton.innerText = 'Ocultar tarifas';
        }
        document.querySelectorAll('.stat-item[data-filter]').forEach(function(item) {
            item.classList.toggle('active-filter', item === card);
            item.setAttribute('aria-pressed', item === card ? 'true' : 'false');
        });
        applyTableFilters();
    }

    window.addEventListener('beforeprint', function() { document.querySelectorAll('.rc-conference details').forEach(function(d) { d.dataset.rcOpen = d.open ? '1' : '0'; d.open = true; }); });
    window.addEventListener('afterprint', function() { document.querySelectorAll('.rc-conference details').forEach(function(d) { d.open = d.dataset.rcOpen === '1'; }); });

    window.onload = function() {
        toggleTarifaRows();

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
        
        if (activeTab === 'graficos') {
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
        var tarifaRows = document.querySelectorAll('tr.tarifa-row');
        var toggleButton = document.getElementById('toggleButton');

        tarifaRows.forEach(function(row) {
            row.classList.toggle('hidden');
        });

        if (toggleButton && toggleButton.innerText === 'MOSTRAR') {
            toggleButton.innerText = 'Ocultar tarifas';
        } else if (toggleButton) {
            toggleButton.innerText = 'Mostrar tarifas';
        }
    }

    function switchTab(tabName) {
        // Remove active class from all tabs and contents
        document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
        
        // Add active class to selected tab and content
        document.getElementById('tab-' + tabName).classList.add('active');
        document.getElementById('content-' + tabName).classList.add('active');
        
        // Atualizar URL com a aba ativa (sem recarregar a página)
        const currentUrl = new URL(window.location.href);
        if (tabName === 'graficos' || tabName === 'ticket' || tabName === 'evolucao' || tabName === 'anomalias') {
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
    <?php include('../../topo.php'); ?>

    <div class="container">
        <header class="rc-page-header"><div><span class="rc-eyebrow">FINANCEIRO / MK-AUTH</span><h1>Conferência de caixa</h1><p>Movimentações, recebimentos e pendências em um só lugar.</p></div><span class="rc-app-badge">Caixa</span></header>

        <!-- TABS -->
        <div class="tabs-container">
            <div class="tabs">
                <button class="tab-button active" id="tab-financeiro" onclick="switchTab('financeiro')">
                    Movimentações
                </button>
                <button class="tab-button" id="tab-graficos" onclick="switchTab('graficos')">
                    Gráficos
                </button>
                <button class="tab-button" id="tab-ticket" onclick="switchTab('ticket')">
                    Ticket médio
                </button>
                <button class="tab-button" id="tab-evolucao" onclick="switchTab('evolucao')">
                    Evolução
                </button>
                <button class="tab-button" id="tab-anomalias" onclick="switchTab('anomalias')">
                    Anomalias
                </button>
            </div>
        </div>

        <?php include('config.php'); ?>

        <?php if ($acesso_permitido) : ?>

            <!-- TAB CONTENT: FINANCEIRO -->
            <div id="content-financeiro" class="tab-content active">
                <div class="content-wrapper">
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
                            <button type="button" id="toggleButton" onclick="toggleTarifaRows()" class="btn btn-secondary">Ocultar tarifas</button>
                        </form>
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

                    <div class="stats-bar">
                        <div class="stat-item boletos" data-filter="entrada" role="button" tabindex="0" aria-pressed="false" title="Mostrar somente boletos recebidos">
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
                    </div>

                    <?php include('conferencia_content.php'); ?>
                    <div class="rc-tools"><div><h2>Lançamentos</h2><p>Consulte os detalhes de cada movimentação.</p></div><div class="rc-tool-actions"><button type="button" class="btn btn-secondary" onclick="rcExport()">Exportar CSV</button> <button type="button" class="btn btn-secondary" onclick="window.print()">Imprimir</button></div></div>
                    <p class="rc-print-period">Período: <?php echo rc_escape($rc_label); ?> · Gerado em <?php echo date('d/m/Y H:i'); ?> · Totais dos cards: consulta enviada ao servidor.</p>
                    <?php if ($result && mysqli_num_rows($result) > 0) : ?>
                        <div class="table-wrapper">
                            <table class="finance-table">
                                <thead>
                                    <tr>
                                        <th>Cliente</th>
                                        <th>Login</th>
                                        <th>Movimentação</th>
                                        <th>Usuário</th>
                                        <th>Histórico</th>
                                        <th>Entrada</th>
                                        <th>Saída</th>
                                        <th>Título</th>
                                        <th>Vencimento</th>
                                    </tr>
                                </thead>
                                <tbody id="financeTableBody">
                                    <?php mysqli_data_seek($result, 0); ?>
                                    <?php $rowNumber = 0; ?>
                                    <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                                        <?php
                                        $nomeClienteClass = ($rowNumber % 2 == 0) ? 'highlight' : '';
                                        $tarifaRowClass = (strpos($row['historico'], 'Tarifa do GerenciaNet') !== false) ? 'tarifa-row' : '';
                                        $matches = array();
                                        preg_match('/t[ií]tulo(?:[^0-9]{0,30})(\d+)/iu', $row['historico'], $matches);
                                        $id = isset($matches[1]) ? $matches[1] : '--';
                                        $cliente_query = "SELECT c.nome, l.login, c.uuid_cliente FROM sis_lanc l
                                            JOIN sis_cliente c ON l.login = c.login
                                            WHERE l.id = '$id'";
                                        $cliente_result = mysqli_query($link, $cliente_query);
                                        $cliente_row = $cliente_result ? mysqli_fetch_assoc($cliente_result) : null;
                                        $nome_cliente = isset($cliente_row['nome']) ? $cliente_row['nome'] : '--';
                                        $login = isset($cliente_row['login']) ? $cliente_row['login'] : '--';
                                        $uuid_cliente = isset($cliente_row['uuid_cliente']) ? $cliente_row['uuid_cliente'] : '';
                                        $movementType = ($row['entrada'] > 0) ? 'entrada' : (($row['saida'] > 0) ? 'saida' : 'outro');
                                        $rowSearch = $nome_cliente . ' ' . $login . ' ' . $row['historico'] . ' ' . $row['usuario'];
                                        ?>
                                        <tr class="<?php echo $nomeClienteClass . ' ' . $tarifaRowClass; ?>" data-movement="<?php echo $movementType; ?>" data-search="<?php echo htmlspecialchars($rowSearch, ENT_QUOTES, 'UTF-8'); ?>">
                                            <td>
                                                <?php

                                                echo '<a href="../../cliente_det.hhvm?uuid=' . htmlspecialchars($uuid_cliente, ENT_QUOTES, 'UTF-8') . '" target="_blank" title="' . htmlspecialchars($nome_cliente, ENT_QUOTES, 'UTF-8') . '">';
                                                echo '<span class="client-icon-clean" aria-hidden="true"><i class="bi bi-person-circle"></i></span>';
                                                echo htmlspecialchars($nome_cliente, ENT_QUOTES, 'UTF-8');
                                                echo '</a>';
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

                                            <td>
                                                <?php if ($row['saida'] > 0): ?>
                                                    <span class="badge badge-danger">R$ <?php echo number_format($row['saida'], 2, ',', '.'); ?></span>
                                                <?php else: ?>
                                                    --
                                                <?php endif; ?>
                                            </td>

                                            <td>
                                                <span class="badge badge-info">
                                                    <img src="img/digital.png" alt="ID" class="icon-sm"> <?php echo $id; ?>
                                                </span>
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
                                        <?php $rowNumber++; ?>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>

                    <?php else : ?>
                        <p class="no-data">Nenhum resultado encontrado.</p>
                    <?php endif; ?>
                </div>
            </div>

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

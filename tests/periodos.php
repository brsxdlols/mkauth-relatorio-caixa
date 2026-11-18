<?php
require dirname(__DIR__).'/addons/rel_caixa/periodos_lib.php';
function period_check($ok,$message){if(!$ok)throw new RuntimeException($message);}
$p=rc_cash_periods(new DateTimeImmutable('2026-11-17'));
period_check($p['hoje']['start']==='2026-11-17'&&$p['hoje']['end']==='2026-11-17','Hoje');
period_check($p['semana']['start']==='2026-11-15'&&$p['semana']['end']==='2026-11-21','Domingo a sábado');
period_check($p['mes']['start']==='2026-11-01'&&$p['mes']['end']==='2026-11-30','Mês atual');
$p=rc_cash_periods(new DateTimeImmutable('2027-01-03'));period_check($p['semana']['start']==='2027-01-03'&&$p['semana']['end']==='2027-01-09','Domingo não volta à semana anterior');
$p=rc_cash_periods(new DateTimeImmutable('2027-01-02'));period_check($p['semana']['start']==='2026-12-27'&&$p['semana']['end']==='2027-01-02','Virada do ano');
$p=rc_cash_periods(new DateTimeImmutable('2028-02-10'));period_check($p['mes']['end']==='2028-02-29','Ano bissexto');
echo "OK: atalhos hoje, semana domingo-sábado, mês e viradas de calendário.\n";

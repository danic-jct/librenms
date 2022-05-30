<?php

// Polls FreeBSD 'netstat -m'  from script via SNMP
use LibreNMS\RRD\RrdDefinition;

$name = 'freebsd-mbuf';
$app_id = $app['app_id'];

if (! empty($agent_data['app'][$name])) {
    $freebsd_mbuf = $agent_data['app'][$name];
} else {
    $options = '-Oqv';
    $oid = '.1.3.6.1.4.1.8072.1.3.2.3.1.2.12.102.114.101.101.98.115.100.45.109.98.117.102';
    $freebsd_mbuf = snmp_get($device, $oid, $options);
}

// Output from  /usr/bin/netstat -m | cut -f1 -d " "

[$mbufs,
$mbufclusters, 
$mbufclusters2,
$fourjumboclusters,
$ninejumboclusters,
$sixteenjumboclusters,
$bytesnetwork,
$mbufsdenied,
$mbufsdelayed,
$jumbodelayed,
$jumbodenied,
$sendfile,
$sendfile2,
$sendfile3,
$sendfile4,
$sendfile5,
$pagesbogus,
$pagesreadheadbyapp,
$agesreadgeadbysendfile,
$sendfilebusy,
$sfbufsdenied,
$sbufsdelayed]  = explode("\n", $freebsd_mbuf);


[$mbufs_current,$mbufs_cache,$mbufs_total] = explode("/",$mbufs);
[$mbufclusters_current,$mbufclusters_cache,$mbufclusters_total,$mbufclusters_max] = explode("/",$mbufclusters);
[$mbufclusters2_current,$mbufclusters2_cache,] = explode("/",$mbufclusters2);
[$fourjumboclusters_current,$fourjumboclusters_cache,$fourjumboclusters_total,$fourjumboclusters_max] = explode("/",$fourjumboclusters);
[$ninejumboclusters_current,$ninejumboclusters_cache,$ninejumboclusters_total,$ninejumboclusters_max] = explode("/",$ninejumboclusters);
[$sixteenjumboclusters_current,$sixteenjumboclusters_cache,$sixteenjumboclusters_total,$sixteenjumboclusters_max] = explode("/",$sixteenjumboclusters);
[$bytesnetwork_current,$bytesnetwork_cache,$bytesnetwork_total] = explode("/",$bytesnetwork);
[$mbufsdenied_mbufs,$mbufsdenied_clusters,$mbufsdenied_total] = explode("/",$mbufsdenied);
[$mbufsdelayed_mbufs,$mbufsdelayed_clusters,$mbufsdelayed_total] = explode("/",$mbufsdelayed);
[$jumbodelayed_4k,$jumbodelayed_9k,$jumbodelayed_16k] = explode("/",$jumbodelayed);
[$jumbodenied_4k,$jumbodenied_9k,$jumbodenied_16k] = explode("/",$jumbodenied);


$rrd_name = ['app', $name, $app_id];

$rrd_def = RrdDefinition::make()
    ->addDataset('current', 'GAUGE', 0, 125000000000)
    ->addDataset('cache', 'GAUGE', 0, 125000000000)
    ->addDataset('total', 'GAUGE', 0, 125000000000)
    ->addDataset('max', 'GAUGE', 0, 125000000000);

$fields = [
    'current'        => $mbufclusters_current,
    'cache'          => $mbufclusters_cache,
    'total'          => $mbufclusters_total,
    'max'            => $mbufclusters_max,
];

$tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');
data_update($device, 'app', $tags, $fields);
update_application($app, $freebsd_mbuf, $fields);

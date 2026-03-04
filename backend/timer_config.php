<?php
$durations = [
    'Washing' => 10,
    'Drying' => 20,
    'Folding' => 5
];
// Build cycle depending on customer's chosen services:
function getStatusCycle($services_string)
{
    $services = strtolower($services_string);
    $cycle = ['Pending Dropoff', 'In Queue'];

    if (str_contains($services, 'wash')) {
        $cycle[] = 'Washing';
        $cycle[] = 'Wash Complete';
    }
    if (str_contains($services, 'dry')) {
        $cycle[] = 'Drying';
        $cycle[] = 'Drying Complete';
    }
    if (str_contains($services, 'fold')) {
        $cycle[] = 'Folding';
        $cycle[] = 'Folding Complete';
    }

    $cycle[] = 'Awaiting Pickup';
    $cycle[] = 'Completed';
    return $cycle;
}
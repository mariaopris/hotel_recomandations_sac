<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Recombee\RecommApi\Client;
use Recombee\RecommApi\Requests as Reqs;
use Recombee\RecommApi\Exceptions as Ex;
use function Webmozart\Assert\Tests\StaticAnalysis\null;

class HotelController extends Controller
{

    public function index()
    {
        //
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }

    public function addDataToRecombee() {
        $client = new Client("sac-project-hotels", 'A8pJ3KAxYdMpqDre5562e7GUREZsl9lFhCWHlQBXNvyQi89uHTku9BA8nVVJ66js', ['region' => 'eu-west']);
        $csvData = array_map('str_getcsv', file(public_path('Hotels.csv')));
        $current_city = $csvData[1][0];
        $counter = 0;
        $index = 0;

        $client->send(new Reqs\AddItemProperty('city', 'string'));
        $client->send(new Reqs\AddItemProperty('free_parking', 'boolean'));
        $client->send(new Reqs\AddItemProperty('free_wifi', 'boolean'));
        $client->send(new Reqs\AddItemProperty('fully_refundable', 'boolean'));
        $client->send(new Reqs\AddItemProperty('name', 'string'));
        $client->send(new Reqs\AddItemProperty('no_prepayment_needed', 'boolean'));
        $client->send(new Reqs\AddItemProperty('num_reviews', 'double'));
        $client->send(new Reqs\AddItemProperty('pool', 'boolean'));
        $client->send(new Reqs\AddItemProperty('prices', 'string'));
        $client->send(new Reqs\AddItemProperty('rank', 'double'));
        $client->send(new Reqs\AddItemProperty('rating', 'double'));
        $client->send(new Reqs\AddItemProperty('avg_price', 'double'));

        for($i = 1; $i < count($csvData); $i++){
            var_dump('line: '. $i);
            if($counter <= 60) {
                $city = $csvData[$i][0];
                $free_parking = $csvData[$i][1];
                $free_wifi = $csvData[$i][2];
                $fully_refundable = $csvData[$i][3];
                $name = $csvData[$i][4];
                $no_prepayment_needed = $csvData[$i][5];
                if(!isset($csvData[$i][6]) || $csvData[$i][6] == '' || $csvData[$i][6] == null || !$csvData[$i][6]) {
                    $csvData[$i][6] = 0;
                } else {
                    $num_reviews = $csvData[$i][6];
                }
                $pool = $csvData[$i][7];
                $prices = $csvData[$i][8];
                $rank = $csvData[$i][9];
                $rating = $csvData[$i][10];
                $avg_price = $csvData[$i][11];
                $counter++;
                $index++;
                $client->send(new Reqs\AddItem($index));
                $client->send(new Reqs\SetItemValues($index, ['city' => $city, 'free_parking'=>$free_parking, 'free_wifi'=>$free_wifi, 'fully_refundable'=>$fully_refundable,
                'name'=>$name, 'no_prepayment_needed'=>$no_prepayment_needed, 'num_reviews' =>$num_reviews, 'pool'=>$pool, 'prices'=>$prices, 'rank'=>$rank, 'rating'=>$rating, 'avg_price'=>$avg_price]));
            }
            if($current_city != $csvData[$i][0]) {
                $counter = 0;
                $current_city = $csvData[$i][0];
            }
        }

        return "Data imported successfully";
    }
}

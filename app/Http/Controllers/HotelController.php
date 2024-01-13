<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Recombee\RecommApi\Client;
use Recombee\RecommApi\Requests as Reqs;
use Recombee\RecommApi\Exceptions as Ex;
use Recombee\RecommApi\Requests\AddDetailView;
use Recombee\RecommApi\Requests\ListItems;
use Recombee\RecommApi\Requests\ListUserDetailViews;
use Recombee\RecommApi\Requests\ListUsers;
use Recombee\RecommApi\Requests\RecommendItemsToItem;
use function Laravel\Prompts\password;
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

    function generateRandomNumberFromString($inputString)
    {
        $hashValue = crc32($inputString);
        $randomNumber = abs($hashValue) % 1000;

        return $randomNumber;
    }

    public function store(Request $request)
    {
        try {
            $username = $request->username;
            $password = $request->password;
            $facilities = $request->facilities;
            $cities = $request->cities;
            $price = $request->price;

            $facilities_text = '';
            if(count($facilities) > 0){
                $facilities_text = '(';
            }
            $check_facility = '';

            foreach ($facilities as $key => $facility){
                $check_facility = 'and';
                if($key == count($facilities) - 1){
                    $facilities_text = $facilities_text . "'". $facility ."' == true)";
                } else {
                    $facilities_text = $facilities_text . "'". $facility ."' == true or ";
                }
            }
            $client = new Client("sac-project-hotels", 'A8pJ3KAxYdMpqDre5562e7GUREZsl9lFhCWHlQBXNvyQi89uHTku9BA8nVVJ66js', ['region' => 'eu-west']);
            $all_results = [];
            foreach ($cities as $key => $city){
                $count = round(15/ count($cities));
                $results = $client->send(new ListItems([
                    'filter' => $facilities_text. $check_facility . "'avg_price' < " . $price . " and " . '"'. $city . '"' . " in 'city' and 'rating' >= 4",
                    'count' => $count,
                    'returnProperties' => true,
                ]));
                foreach ($results as $result){
                    $all_results[] = $result;
                }
            }

            $userId = $this->generateRandomNumberFromString($username . $password);
            $userExists = $client->send(new Reqs\AddUser($userId));

            $client->send(new Reqs\SetUserValues($userId, ['username' => $username]));
            $client->send(new Reqs\SetUserValues($userId, ['password' => $password]));

            foreach ($all_results as $result) {
                $client->send(new AddDetailView($userId, $result['itemId']));
            }


        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => "Error adding user: " . $e->getMessage()]);
        }

        return response()->json(['status' => true, 'message' => 'User saved successfully!', 'user_id' => $userId]);
    }

    public function emptyUsersTable(){
        try {
            $client = new Client("sac-project-hotels", 'A8pJ3KAxYdMpqDre5562e7GUREZsl9lFhCWHlQBXNvyQi89uHTku9BA8nVVJ66js', ['region' => 'eu-west']);

            // Retrieve all user IDs
            $userIds = $client->send(new Reqs\ListUsers());

            // Delete each user
            foreach ($userIds as $userId) {
                $client->send(new Reqs\DeleteUser($userId));
            }

            return response()->json(['status' => true, 'message' => 'Users table emptied successfully.']);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => "Error emptying users table: " . $e->getMessage()]);
        }
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
        $client->send(new Reqs\AddItemProperty('avg_price', 'int'));

        for($i = 1; $i < count($csvData); $i++){
            var_dump('line: '. $i);
//            if($counter <= 60) {
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
                $avg_price = round($csvData[$i][11] * 0.011);
                $counter++;
                $index++;

                if($avg_price != '0'){
                    $client->send(new Reqs\AddItem($index));
                    $client->send(new Reqs\SetItemValues($index, ['city' => $city, 'free_parking'=>$free_parking, 'free_wifi'=>$free_wifi, 'fully_refundable'=>$fully_refundable,
                        'name'=>$name, 'no_prepayment_needed'=>$no_prepayment_needed, 'num_reviews' =>$num_reviews, 'pool'=>$pool, 'prices'=>$prices, 'rank'=>$rank, 'rating'=>$rating, 'avg_price'=>$avg_price]));
                }
//                $client->send(new Reqs\AddItem($index));
//                $client->send(new Reqs\SetItemValues($index, ['city' => $city, 'free_parking'=>$free_parking, 'free_wifi'=>$free_wifi, 'fully_refundable'=>$fully_refundable,
//                'name'=>$name, 'no_prepayment_needed'=>$no_prepayment_needed, 'num_reviews' =>$num_reviews, 'pool'=>$pool, 'prices'=>$prices, 'rank'=>$rank, 'rating'=>$rating, 'avg_price'=>$avg_price]));
//            }
//            if($current_city != $csvData[$i][0]) {
//                $counter = 0;
//                $current_city = $csvData[$i][0];
//            }
        }

        $client->send(new Reqs\AddUserProperty('username', 'string'));
        $client->send(new Reqs\AddUserProperty('password', 'string'));

        return "Data imported successfully";
    }

    public function emptyItemsTable()
    {
        try {
            $client = new Client("sac-project-hotels", 'A8pJ3KAxYdMpqDre5562e7GUREZsl9lFhCWHlQBXNvyQi89uHTku9BA8nVVJ66js', ['region' => 'eu-west']);
            $itemIds = $client->send(new Reqs\ListItems());

            foreach ($itemIds as $itemId) {
                $client->send(new Reqs\DeleteItem($itemId));
            }

            return response()->json(['status' => true, 'message' => 'Items table emptied successfully.']);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => "Error emptying items table: " . $e->getMessage()]);
        }
    }

    public function getPopularHotels()
    {
        $client = new Client("sac-project-hotels", 'A8pJ3KAxYdMpqDre5562e7GUREZsl9lFhCWHlQBXNvyQi89uHTku9BA8nVVJ66js', ['region' => 'eu-west']);

        $result = $client->send(new ListItems([
                    'filter' => "'num_reviews' > 3000 and 'rating' > 4.5",
                'count' => 10, 'returnProperties' => true,
                ]));

        return $result;
    }

    public function getRecommandations(Request $request)
    {
        $client = new Client("sac-project-hotels", 'A8pJ3KAxYdMpqDre5562e7GUREZsl9lFhCWHlQBXNvyQi89uHTku9BA8nVVJ66js', ['region' => 'eu-west']);
        $hotels = $client->send(new ListUserDetailViews($request->user_id));
        $item_id = $hotels[rand(0, count($hotels) - 1)]['itemId'];
        $result = $client->send(new RecommendItemsToItem($item_id, $request->user_id, 8, ['filter' => "'rating' > 3.5 and 'num_reviews' > 200", 'returnProperties' => true]));
        $result_long_tail = $client->send(new RecommendItemsToItem($item_id, $request->user_id, 2, ['filter' => "'rating' > 3.5 and 'num_reviews' < 50", 'returnProperties' => true]));
        $result['recomms'][] = $result_long_tail['recomms'][0];
        $result['recomms'][] = $result_long_tail['recomms'][1];
        return $result;
    }

    public function login(Request $request) {
        $client = new Client("sac-project-hotels", 'A8pJ3KAxYdMpqDre5562e7GUREZsl9lFhCWHlQBXNvyQi89uHTku9BA8nVVJ66js', ['region' => 'eu-west']);

        $user_name = $request->user_name;
        $password = $request->password;

        $user = $client->send(new ListUsers([
            'filter' => '"' . $user_name .'" ' . "in 'username' and " . '"' .$password . '" ' . "in 'password'",
            'returnProperties' => true,
        ]));

        if(count($user) == 1)
        {
            return response()->json(['status' => true, 'user' => $user[0]]);
        }

        return response()->json(['status' => false, 'message' => 'Error']);
    }

    public function getHotelsByCity(Request $request) {

        $client = new Client("sac-project-hotels", 'A8pJ3KAxYdMpqDre5562e7GUREZsl9lFhCWHlQBXNvyQi89uHTku9BA8nVVJ66js', ['region' => 'eu-west']);

        $results = $client->send(new ListItems([
            'filter' => '"' . $request->city .'" in ' . "'city'",
            'returnProperties' => true,
        ]));

        return $results;
    }

    public function setHotelAsViewed(Request $request) {
        $user_id = $request->user_id;
        $item_id = $request->item_id;
        $client = new Client("sac-project-hotels", 'A8pJ3KAxYdMpqDre5562e7GUREZsl9lFhCWHlQBXNvyQi89uHTku9BA8nVVJ66js', ['region' => 'eu-west']);
        $client->send(new AddDetailView($user_id, $item_id));
    }
}



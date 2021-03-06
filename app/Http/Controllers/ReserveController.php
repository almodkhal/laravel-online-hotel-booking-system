<?php

namespace App\Http\Controllers;

use App\Data;
use App\DataProperty;
use App\Http\Controllers\Base\BaseController;
use App\Http\Controllers\Navigation\NavigationController;
use App\Http\Controllers\User\UserController;
use App\Libraries\Utilities\BaseUtility;
use App\Libraries\Utilities\ItemUtility;
use App\Reserve;
use DB;
use Illuminate\Http\Request;
use Route;
use Validator;

class ReserveController extends Controller
{
    public function index(Request $request, $type, $filters = null)
    {

        $data = BaseUtility::generateForIndex($type);
        $data ['datas'] = Reserve::all();
        return view("admin.items.views.subviews.reserve.index", $data);
    }

    public function create($type)
    {
        $data = BaseUtility::generateForCreate($type);
        $data['groups'] = ItemUtility::getPropertiesForInput(Route::currentRouteName(), Route::current()->parameters());
        $data['components'] = ItemUtility::getRequiredComponents($data['groups']);
        return view("admin.items.views.subviews.reserve.form", $data);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $type)
    {

//        self::checkType($type);
//        self::checkTables();

//        $ruels = self::getItemsValidationRules(Route::currentRouteName(), Route::current()->parameters());
//        dd($ruels);
        $validator = Validator::make(
            $request->all(),
            ItemUtility::getItemsValidationRules(Route::currentRouteName(), Route::current()->parameters())
        );
        if ($validator->passes()) {

//            dd($request->toArray());
            $received_data = $request->toArray();
//            $separated_data = self::separateReceivedData($received_data);
            $separated_data = ItemUtility::separateReceivedData($type, $received_data);

            $r = new Reserve();
            $r->date = $request->input('date');
            $r->start_date = $request->input('start_date');
            $r->end_date = $request->input('end_date');
            $r->price = $request->input('price');
            $r->active = $request->input('active');
            $r->code = $request->input('code');
            $r->situation = $request->input('situation');
            $r->check = $request->input('check');
            $r->save();
            $r_id = $r->id;
            return response()->json(['success' => 'Added new records.']);

        }
        return response()->json(['error' => $validator->errors()->all()]);
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Data $data
     * @return \Illuminate\Http\Response
     */
    public function show($type, $id)
    {
        $data = BaseController::createBaseInformations();

        $user = UserController::getCurrentUserData();
        $data ['user'] = $user;

        $data['navigations'] = NavigationController::getNavigation('admin');

        return view("items.index", $data);

        //
    }

    public function get(Request $request, $type)
    {


        $id = $request->input('id');

        $r = Reserve::find($id);
        if ($r->situation == 1) {
            $request_title = 'درخواست تایید اتاق';
        } elseif ($r->situation == 5) {
            $request_title = 'درخواست تایید فیش پرداختی';
        } else {
            $request_title = 'مشخص نیست';
        }

        return response()->json(['error' => false, 'message' => 'success', 'reserve' => $r, 'request_title' => $request_title]);

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Data $data
     * @return \Illuminate\Http\Response
     */
    public function edit($type, $id)
    {
        $data = BaseUtility::generateForEdit($type, $id);
        $data['groups'] = ItemUtility::getPropertiesForInput(Route::currentRouteName(), Route::current()->parameters());
        $data['components'] = ItemUtility::getRequiredComponents($data['groups']);
        return view("admin.items.views.subviews.reserve.form", $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Data $data
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $type, $id)
    {

//        self::checkType($type);
//        self::checkTables();

        $validator = Validator::make(
            $request->all(),
            ItemUtility::getItemsValidationRules(Route::currentRouteName(), Route::current()->parameters())
        );
        if ($validator->passes()) {

            $received_data = $request->toArray();
//            $separated_data = self::separateReceivedData($received_data);
            $separated_data = ItemUtility::separateReceivedData($type, $received_data);
            $r = Reserve::find($id);
            $r->date = $request->input('date');
            $r->start_date = $request->input('start_date');
            $r->end_date = $request->input('end_date');
            $r->price = $request->input('price');
            $r->active = $request->input('active');
            $r->code = $request->input('code');
            $r->situation = $request->input('situation');
            $r->check = $request->input('check');
            $r->save();
            return response()->json(['success' => 'Added new records.']);

//            dd($separated_data);

            return response()->json(['success' => 'Added new records.']);
        }
        return response()->json(['error' => $validator->errors()->all()]);
//        return redirect()->route("data.index", ['type' => $type]);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Data $data
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $type)
    {

        $id = $request->input('id');
        ItemUtility::deleteData($type, $id);

        return response()->json(['error' => 0, 'message' => 'id is : ' . $id]);
    }


    public function changeProperty(Request $request, $type)
    {
        $did = $request->input('id');
        $value = $request->input('value');
        $key = $request->input('key');
//        return response()->json(["error" => 0, 'message' => "dassasdadds"]);
//        return response()->json(["error" => 0, 'message' => $did . " " . $value . " " . $key]);
        $dtid = Data::where('id', '=', $did)->get();
        $p = DataProperty::where('title', '=', $key)->where('type', '=', $dtid[0]->type)->get();

        $pid = $p[0]->id;

        DB::table('data_assigned_properties')
            ->where('data', '=', $did)
            ->where('property', '=', $pid)
            ->update([
                'value' => $value
            ]);

//        return response()->json(["error" => 0, 'message' => $pid ]);

        return response()->json(["error" => 0, 'message' => 'success']);
    }


    public function refresh(Request $request)
    {

        $did = $request->input('id');
        $value = $request->input('value');
        $key = $request->input('key');

        $dtid = Data::where('id', '=', $did)->get();

        $p = DataProperty::where('title', '=', $key)->where('type', '=', $dtid[0]->type)->get();
        $pid = $p[0]->id;

        DB::table('data_assigned_properties')
            ->where('data', ' = ', $did)
            ->where('property', ' = ', $pid)
            ->update([
                'value' => $value
            ]);

        return response()->json(["error" => 0, 'message' => 'success']);
    }

    public function getReserves($situations)
    {
        $sits = explode(',', $situations);
        $ress = DB::table('reserves')->whereIn('situation', $sits)->get();
        return response()->json(["error" => 0, 'message' => 'success', 'reserves' => $ress]);

    }

    public function setProperty(Request $request, $type, $property)
    {
        if ($property == "situation") {

            $id = $request->input('id');
            $value = $request->input('value');

            $r = Reserve::find($id);

            if ($r->situation == 1) {
                if ($value == "confirm") {
                    $r->situation = 3;
                    $r->save();
                } elseif ($value == "reject") {
                    $r->situation = 2;
                    $r->save();
                }
            } elseif ($r->situation == 5) {
                if ($value == "confirm") {
                    $r->situation = 7;
                    $r->save();
                } elseif ($value == "reject") {
                    $r->situation = 6;
                    $r->save();
                }
            }
            return response()->json(["error" => 0, 'message' => "success"]);
        }
    }

}

<?php

namespace App\Http\Controllers;

use App\Comment;
use App\Data;
use App\DataProperty;
use App\Http\Controllers\Base\BaseController;
use App\Http\Controllers\Navigation\NavigationController;
use App\Http\Controllers\User\UserController;
use App\Libraries\Utilities\BaseUtility;
use App\Libraries\Utilities\ItemUtility;
use DB;
use Illuminate\Http\Request;
use Route;
use Validator;

class CommentController extends Controller
{
    public function index(Request $request, $type, $filters = null)
    {

        $data = BaseUtility::generateForIndex($type);
        $data ['datas'] = Comment::all();
        return view("admin.items.views.subviews.comment.index", $data);
    }

    public function create($type)
    {
        $data = BaseUtility::generateForCreate($type);
        $data['groups'] = ItemUtility::getPropertiesForInput(Route::currentRouteName(), Route::current()->parameters());
        $data['components'] = ItemUtility::getRequiredComponents($data['groups']);
        return view("admin.items.views.subviews.comment.form", $data);

    }

    public function store(Request $request, $type)
    {
        $validator = Validator::make($request->all(), ItemUtility::getItemsValidationRules(Route::currentRouteName(), Route::current()->parameters()));
        if ($validator->passes()) {
            $received_data = $request->toArray();
            $separated_data = ItemUtility::separateReceivedData($type, $received_data);
            $r = new Comment();
            $r->content = $request->input('content');
            $r->sender = $request->input('sender');
            $r->date = $request->input('date');
            $r->room = $request->input('room');
            $r->reply_to = $request->input('reply_to');
            $r->save();
            $r_id = $r->id;


//            ItemUtility::storeData($type, $separated_data['item'], $separated_data['property']);
        }
        return response()->json(['error' => $validator->errors()->all()]);
    }

    public function show($type, $id)
    {
        $data = BaseController::createBaseInformations();
        $user = UserController::getCurrentUserData();
        $data ['user'] = $user;
        $data['navigations'] = NavigationController::getNavigation('admin');
        return view("items.index", $data);
    }

    public function edit($type, $id)
    {
        $data = BaseUtility::generateForEdit($type, $id);
        $data['groups'] = ItemUtility::getPropertiesForInput(Route::currentRouteName(), Route::current()->parameters());
        $data['components'] = ItemUtility::getRequiredComponents($data['groups']);
        return view("admin.items.views.subviews.comment.form", $data);

    }

    public function update(Request $request, $type, $id)
    {

        $validator = Validator::make($request->all(), ItemUtility::getItemsValidationRules(Route::currentRouteName(), Route::current()->parameters()));
        if ($validator->passes()) {
            $received_data = $request->toArray();
            $separated_data = ItemUtility::separateReceivedData($type, $received_data);

            $r = Comment::find($id);
            $r->content = $request->input('content');
            $r->sender = $request->input('sender');
            $r->date = $request->input('date');
            $r->room = $request->input('room');
            $r->reply_to = $request->input('reply_to');
            $r->save();
//            ItemUtility::storeData($type, $separated_data['item'], $separated_data['property'], $id);
            return response()->json(['success' => 'Added new records.']);
        }
        return response()->json(['error' => $validator->errors()->all()]);
    }

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
}

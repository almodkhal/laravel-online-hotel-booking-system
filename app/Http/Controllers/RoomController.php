<?php

namespace App\Http\Controllers;

use App\Data;
use App\DataProperty;
use App\Http\Controllers\Base\BaseController;
use App\Http\Controllers\Navigation\NavigationController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\Widget\WidgetController;
use App\Libraries\Utilities\BaseUtility;
use App\Libraries\Utilities\BreadcrumbsUtility;
use App\Libraries\Utilities\ItemUtility;
use App\Libraries\Utilities\NavigationUtility;
use App\Libraries\Utilities\TextUtility;
use App\Room;
use App\Rules\CheckLanguage;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Libraries\Utilities\PluralUtility;

use Route;
use Validator;

class RoomController extends Controller
{

    public function index(Request $request, $type, $filters = null)
    {
        $data = BaseUtility::generateForIndex($type);
        $data ['datas'] = ItemUtility::getItems($type);
        return view("admin.items.views.subviews.room", $data);
    }

    public function create($type)
    {
        $data = BaseUtility::generateForCreate($type);
        $data['groups'] = ItemUtility::getPropertiesForInput(Route::currentRouteName(), Route::current()->parameters());
        $data['components'] = ItemUtility::getRequiredComponents($data['groups']);
        return view("admin.items.views.create", $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $type)
    {

        $validator = Validator::make($request->all(), ItemUtility::getItemsValidationRules(Route::currentRouteName(), Route::current()->parameters()));
        if ($validator->passes()) {
            $received_data = $request->toArray();
            $separated_data = ItemUtility::separateReceivedData($type, $received_data);
            ItemUtility::storeData($type, $separated_data['item'], $separated_data['property']);
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
        return view("admin.items.views.edit", $data);
    }

    public function update(Request $request, $type, $id)
    {

        $validator = Validator::make($request->all(), ItemUtility::getItemsValidationRules(Route::currentRouteName(), Route::current()->parameters()));
        if ($validator->passes()) {
            $received_data = $request->toArray();
            $separated_data = ItemUtility::separateReceivedData($type, $received_data);
            ItemUtility::storeData($type, $separated_data['item'], $separated_data['property'], $id);
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


}
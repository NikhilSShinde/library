<?php
namespace App\PiplModules\vehicle\Controllers;
use Auth;
use Auth\User;
use App\Http\Requests;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use Storage;
use App\PiplModules\vehicle\Models\UserVehicleInformation;
use App\PiplModules\vehicle\Models\DriverVehicleInformation;
use Mail;
use Datatables;
use Intervention\Image\Facades\Image;

class VehicleController extends Controller {

    public function index($user_id='0') {
       
        if (Auth::user()) {
            return view("vehicle::list")->with('user_id', $user_id);
        } else {
            $errorMsg = "Error! Something is wrong going on.";
            Auth::logout();
            return redirect("admin/login")->with("issue-profile", $errorMsg);
        }
    }

    public function getVehicleInfomation($user_id) {
        if($user_id!='' && $user_id!='0')
        {
           $all_data = UserVehicleInformation::where('user_id', $user_id)->get();
        }else{
            $all_data = UserVehicleInformation::where('user_id', Auth::user()->id)->get();
        }
        $all_data =$all_data->sortByDesc('id');
        return Datatables::of($all_data)
                        ->addcolumn('user_name', function($all_data) {
                            return $all_data->UserInformation->first_name . ' ' . $all_data->UserInformation->last_name;
                        })
                        ->addcolumn('status', function($all_data) {
                            return ($all_data->status == '0') ? 'Inactive' : 'Active';
                        })
                        ->make(true);
    }

    public function updateVehicleInfomation(Request $request, $vehicle_id) {
        if (Auth::user()) {
            $vehicle_data = UserVehicleInformation::find($vehicle_id);
            if ($request->method() == "GET") {
                 return view("vehicle::edit", array('arr_vehicle' => $vehicle_data));
            } else {
                $data = $request->all();
                $validate_response = Validator::make($data, array(
                            'vehicle_name' => 'required',
                            'vehicle_desc' => 'required',
                            'status' => 'required'
                ));
                if ($validate_response->fails()) {
                    return redirect($request->url())->withErrors($validate_response)->withInput();
                } else {
                    
                    if ($request->hasFile('vehicle_image')) {
                        
                        $uploaded_file = $request->file('vehicle_image');
                        $extension = $uploaded_file->getClientOriginalExtension();
                        $new_file_name = str_replace(".", "-", microtime(true)) . "." . $extension;
                        $path=  realpath(dirname(__FILE__).'/../../../../');	
                        $old_file=$path.'/storage/app/public/vehicle-images/'.$new_file_name;
                        $new_file=$path.'/storage/app/public/vehicle-images/'.$new_file_name;
                        Storage::put('public/vehicle-images/'.$new_file_name,file_get_contents($request->file('vehicle_image')->getRealPath()));
                        $command="convert ".$old_file." -resize 300x200^ ".$new_file;
                       
                        $vehicle_data->vehicle_image = $new_file_name;
                    }
                    if ($request->hasFile('plate_number_image')) {
                        
                        $uploaded_file = $request->file('plate_number_image');
                        $extension = $uploaded_file->getClientOriginalExtension();
                        $new_file_name1 = str_replace(".", "-", microtime(true)) . "." . $extension;
                        $path=  realpath(dirname(__FILE__).'/../../../../');	
                        $old_file1=$path.'/storage/app/public/vehicle-number-images/'.$new_file_name1;
                        $new_file1=$path.'/storage/app/public/vehicle-number-images/'.$new_file_name1;
                        Storage::put('public/vehicle-number-images/'.$new_file_name1,file_get_contents($request->file('plate_number_image')->getRealPath()));
                        $command="convert ".$old_file1." -resize 300x200^ ".$new_file1;
                       
                        $vehicle_data->plate_number_image = $new_file_name1;
                    }
                    $vehicle_data->vehicle_name = $request->vehicle_name;
                    $vehicle_data->vehicle_desc = $request->vehicle_desc;
                    $vehicle_data->financial_type = $request->financial_type;
                    $vehicle_data->year_manufacture = $request->year_manufacture;
                    $vehicle_data->plate_number = $request->plate_number;
                    $vehicle_data->status = $request->status;
                    $vehicle_data->save();
                    return redirect('admin/vehicle-list/'.$vehicle_data->user_id)->with('status', 'Vehicle information updated successfully!');
                }
            }
        }
    }

    public function addVehicleInfomation(Request $request, $user_id) {
        if (Auth::user()) {
            if ($request->method() == "GET") {
                  $user_vehicles = UserVehicleInformation::where('user_id', Auth::user()->id)->get();
                return view("vehicle::create",array("user_id"=>$user_id,"user_vehicles"=>$user_vehicles));
            } else {
                $data = $request->all();
               if($data['type']=='0')
               {
                $validate_response = Validator::make($data, array(
                            'vehicle_name' => 'required',
                            'vehicle_desc' => 'required',
                            'year_manufacture' => 'required',
                            'plate_number' => 'required',
                            'financial_type' => 'required',
                            'vehicle_desc' => 'required',
                            'status' => 'required'
                ),array(
                        'vehicle_name.required' => 'Please enter car make',
                   ));
                }else{
                      $validate_response = Validator::make($data, array(
                            'vehicle_list' => 'required'
                ),array(
                        'vehicle_list.required' => 'Please select a vehicle',
                   ));
                }
                if ($validate_response->fails()) {
                    return redirect($request->url())->withErrors($validate_response)->withInput();
                } else {
                  if($data['type']=='0')
                  { 
                    $vehicle_data = new UserVehicleInformation();
                    
                    if ($request->hasFile('vehicle_image')) {
                        $uploaded_file = $request->file('vehicle_image');
                        $extension = $uploaded_file->getClientOriginalExtension();
                        $new_file_name = str_replace(".", "-", microtime(true)) . "." . $extension;
                        $path=  realpath(dirname(__FILE__).'/../../../../');	
                        $old_file=$path.'/storage/app/public/vehicle-images/'.$new_file_name;
                        $new_file=$path.'/storage/app/public/vehicle-images/'.$new_file_name;
                        Storage::put('public/vehicle-images/'.$new_file_name,file_get_contents($request->file('vehicle_image')->getRealPath()));
                        $command="convert ".$old_file." -resize 300x200^ ".$new_file;
                        exec($command);
                      //  Storage::put('public/vehicle-images/' . $new_file_name, file_get_contents($uploaded_file->getRealPath()));
                        $vehicle_data->vehicle_image = $new_file_name;
                    }
                    if ($request->hasFile('plate_number_image')) {
                        
                        $uploaded_file = $request->file('plate_number_image');
                        $extension = $uploaded_file->getClientOriginalExtension();
                        $new_file_name1 = str_replace(".", "-", microtime(true)) . "." . $extension;
                        $path=  realpath(dirname(__FILE__).'/../../../../');	
                        $old_file1=$path.'/storage/app/public/vehicle-number-images/'.$new_file_name1;
                        $new_file1=$path.'/storage/app/public/vehicle-number-images/'.$new_file_name1;
                        Storage::put('public/vehicle-number-images/'.$new_file_name1,file_get_contents($request->file('plate_number_image')->getRealPath()));
                        $command="convert ".$old_file1." -resize 300x200^ ".$new_file1;
                        exec($command);
                      //  Storage::put('public/vehicle-images/' . $new_file_name, file_get_contents($uploaded_file->getRealPath()));
                        $vehicle_data->plate_number_image = $new_file_name1;
                    }
                    if($user_id!='' && $user_id!='0')
                    {
                         $vehicle_data->user_id = $user_id;
                    }else{
                         $vehicle_data->user_id = Auth::user()->id;
                         $vehicle_data->added_by_agent =1;
                    }
                   
                    $vehicle_data->vehicle_name = $request->vehicle_name;
                    $vehicle_data->plate_number = $request->plate_number;
                    $vehicle_data->financial_type = $request->financial_type;
                    $vehicle_data->year_manufacture = $request->year_manufacture;
                    $vehicle_data->vehicle_desc = $request->vehicle_desc;
                    $vehicle_data->status = $request->status;
                    $vehicle_data->save();
                }else{
                    $userVehicleInfo=UserVehicleInformation::where('id',$request->vehicle_list)->first();
                    $arrVehicleInformationData=array();
                    if($user_id!='' && $user_id!='0')
                    {
                         $arrVehicleInformationData['user_id'] = $user_id;
                         $arrVehicleInformationData['vehicle_id'] = $userVehicleInfo->id;
                    }
                    DriverAssignedDetail::create($arrVehicleInformationData);
//                    $arrVehicleInformationData['vehicle_image'] = $userVehicleInfo->vehicle_image;
//                     $arrVehicleInformationData['plate_number_image'] = $userVehicleInfo->plate_number_image;
//                    $arrVehicleInformationData['vehicle_name'] = $userVehicleInfo->vehicle_name;
//                    $arrVehicleInformationData['plate_number'] = $userVehicleInfo->plate_number;
//                    $arrVehicleInformationData['vehicle_desc'] = $userVehicleInfo->vehicle_desc;
//                    $arrVehicleInformationData['status'] = $userVehicleInfo->status;
//                   
//                    UserVehicleInformation::create($arrVehicleInformationData);
                }
                    
                    if($user_id!='' && $user_id!='0')
                    {
                      return redirect('admin/vehicle-list/' . $user_id)->with('status', 'Vehicle information addedd successfully!');
                    }else{
                        return redirect('admin/vehicle-list/')->with('status', 'Vehicle information addedd successfully!');
                    }
                }
            }
        }
    }

    public function deleteSelectedVehicle($vehicle_id) {
        $vehicle_id = UserVehicleInformation::find($vehicle_id);
        if ($vehicle_id) {
            $vehicle_id->delete();
            echo json_encode(array("success" => '1', 'msg' => 'Selected records has been deleted successfully.'));
        } else {
            echo json_encode(array("success" => '0', 'msg' => 'There is an issue in deleting records.'));
        }
    }

}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Document;
use App\Models\User;
use App\Models\Address;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class MemberController extends Controller
{
    protected function redirect()
    {
        return redirect('/index');
    }

    protected function index()
    {
        return $this->view();
    }

    protected function getUserList()
    {
        return $this->user::all();
    }

    protected function userDetail($param)
    {
        return view($this->path, ['id' => $param]);
    }

    protected function getDetail()
    {
        extract($this->post);
        $user = $this->user::find($id)->makeVisible('password');
        $user_picture = Document::whereHas('documentable', function ($query) use ($user) {
            $query->where('id', $user->id)
                  ->where('documentable_type', User::class);
        })->first();
        $user->user_picture = $user_picture['file_path'];
        $address = $this->address::where('user_id', $id)
        ->join('address_types', 'address.address_type_id', '=', 'address_types.id')
        ->select('address.*', 'address_types.name as address_type_name')
        ->first();
        $proof = Document::whereHas('documentable', function ($query) use ($address) {
            $query->where('id', $address->id)
                  ->where('documentable_type', Address::class);
        })->first();
        $address->proof = $proof['file_path'];
        $userArray = $user->toArray();
        $addressArray = $address->toArray();
        $mergedData = array_merge($userArray, $addressArray);
        return response()->json($mergedData, 200);
    }

    protected function register($param = false)
    {
        if($param){
            return view($this->path, ['id' => $param]);
        }
        return $this->view();
    }

    protected function validateFile()
    {
        $proof = $this->request->file('proof');
        $user_picture = $this->request->file('user_picture');
        $fileData = [
            'proof' => $proof,
            'user_picture' => $user_picture,
        ];
        $fileRule = [
            'proof' => 'required|file|max:2048|mimes:jpeg,png,jpg',
            'user_picture' => 'required|file|max:2048|mimes:jpeg,png,jpg',
        ];
        $fileMessage = [
            'proof.required' => '請上傳證明文件。',
            'proof.max' => '上傳文件大小不得超過2MB。',
            'proof.mimes' => '上傳文件必須是 JPEG、PNG 或 JPG。',
            'user_picture.required' => '請上傳用戶圖片。',
            'user_picture.max' => '上傳文件大小不得超過2MB。',
            'user_picture.mimes' => '上傳文件必須是 JPEG、PNG 或 JPG。',
        ];
        if($proof && !$user_picture){
            $fileData = [
                'proof' => $proof,
            ];
            $fileRule = [
                'proof' => 'required|file|max:2048|mimes:jpeg,png,jpg',
            ];
            $fileMessage = [
                'proof.required' => '請上傳證明文件。',
                'proof.max' => '上傳文件大小不得超過2MB。',
                'proof.mimes' => '上傳文件必須是 JPEG、PNG 或 JPG。',
            ];
        }else if(!$proof && $user_picture){
            $fileData = [
                'user_picture' => $user_picture,
            ];
            $fileRule = [
                'user_picture' => 'required|file|max:2048|mimes:jpeg,png,jpg',
            ];
            $fileMessage = [
                'user_picture.required' => '請上傳用戶圖片。',
                'user_picture.max' => '上傳文件大小不得超過2MB。',
                'user_picture.mimes' => '上傳文件必須是 JPEG、PNG 或 JPG。',
            ];
        }
        $validator = Validator::make($fileData, $fileRule, $fileMessage);
        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return response()->json(['errors' => $errors], 422); 
        }else{
            if($proof){
                $proofFileName = date('YmdHis') . '_' . $proof->getClientOriginalName();
                $proof->move(public_path('uploads'), $proofFileName);
            }
            if($user_picture){
                $userPictureFileName = date('YmdHis') . '_' . $user_picture->getClientOriginalName();
                $user_picture->move(public_path('uploads'), $userPictureFileName);
            }
            $filePath = [
                'proof' => $proofFileName??false,
                'user_picture' => $userPictureFileName??false,
            ];
            return response()->json(['data' => $filePath], 200);
        }
    }

    protected function saveForm()
    {
        extract($this->post);
        $rules = [
            'email' => 'required|email|unique:users,email',
        ];
        $validator = Validator::make(['email' => $users['email']], $rules,[
            'email.required' => 'email不可為空',
            'email.email' => 'email格式錯誤',
            'email.unique' => '郵件已被使用',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return response()->json(['errors' => $errors], 422); 
        }
        $user = $this->user::create($users);
        $user_id = $user['id'];
        $address['user_id'] = $user_id;
        $address = $this->address::create($address);
        $address_id = $address['id'];
        $user = $this->user::find($user_id);
        $address = $this->address::find($address_id);
        $document = new Document([
            'file_path' => "/uploads/".$documents['user_picture'],
        ]);
        $document->documentable()->associate($user);
        $document->save();
        $document = new Document([
            'file_path' => "/uploads/".$documents['proof'],
        ]);
        $document->documentable()->associate($address);
        $document->save();
        return response()->json([], 200); 
    }

    protected function updateForm()
    {
        extract($this->post);
        $data = $this->user::where('id', '!=', $id['id'])->where('email', $users['email'])->get();
        if(count($data)){
            return response()->json(['errors' => ['郵件已被使用']], 422);
        }
        $user = $this->user::find($id['id']);
        $user->update($users);

        $addressUpdate = $this->address::where('user_id', $id['id'])->first();
        $addressUpdate->update($address);

        $this->document::where('documentable_type', 'App\Models\User')
        ->where('documentable_id', $id['id'])->update(['file_path' => '/uploads/'.$documents['user_picture']]);
        $this->document::where('documentable_type', 'App\Models\Address')
        ->where('documentable_id', $addressUpdate['id'])->update(['file_path' => '/uploads/'.$documents['proof']]);
    }

    protected function getCityList()
    {
        return $this->city::all();
    }

    protected function getAreaList()
    {
        extract($this->post);
        return $this->area::where('city_id', $id)->get();
    }

    protected function getAddressType()
    {
        return $this->address_type::all();
    }

    protected function exportExcel()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', '姓名');
        $sheet->setCellValue('B1', 'Email');
        $sheet->setCellValue('C1', '生日');
        $sheet->setCellValue('D1', 'Address Type');
        $sheet->setCellValue('E1', '郵遞區號');
        $sheet->setCellValue('F1', '地址');
        $sheet->setCellValue('G1', '密碼');

        $users = $this->user::all()->makeVisible('password')->toArray();
        foreach($users as $user){
            $address = $this->address::where('user_id', $user['id'])
            ->join('address_types', 'address.address_type_id', '=', 'address_types.id')
            ->select('address.*', 'address_types.name as address_type_name')
            ->first();
            $address = $address->toArray();
            $merge = array_merge($user, $address);
            extract($merge);
            $excelData = [$last_name.$first_name, $email, $birthdate, $address_type_name, $zipcode, $city.$country.$address, $password];
            $data[] = $excelData;
        }

        foreach ($data as $rowIndex => $row) {
            foreach ($row as $columnIndex => $value) {
                $sheet->setCellValueByColumnAndRow($columnIndex + 1, $rowIndex + 2, $value);
            }
        }

        $fileName = tempnam(sys_get_temp_dir(), 'excel');
        $writer = new Xlsx($spreadsheet);
        $writer->save($fileName);

        return response()->download($fileName, 'user.xlsx')->deleteFileAfterSend(true);
    }

    protected function userDelete()
    {
        extract($this->post);
        $user = $this->user::find($id)->delete();
        $address = $this->address::where('user_id', $id)->first();
        $address->delete();
        $this->document::where('documentable_type', 'App\Models\User')
        ->where('documentable_id', $id)->delete();
        $this->document::where('documentable_type', 'App\Models\Address')
        ->where('documentable_id', $address['id'])->delete();
    }

}

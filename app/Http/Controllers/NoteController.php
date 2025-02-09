<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Traits\GeneralTrait;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    use GeneralTrait;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $user=auth('sanctum')->user();
            $notes = Note::whereBelongsTo($user)->orderBy('updated_at', 'desc')->get();

            return $this->returnData('notes',$notes);
        }catch (\Exception $ex){
            return $this->returnError(500,$ex->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $user=auth('sanctum')->user();
            $note=new Note();
            $note->title=$request->title;
            $note->content=$request->note_content;
            $note->user_id=$user->id;
            $note->save();

            return $this->returnData('note',$note,'تم الاضافة بنجاح!');
        }catch (\Exception $ex){
            return $this->returnError(500,$ex->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $note_title)
    {
        try {
            $user=auth('sanctum')->user();
            $notes=Note::whereBelongsTo($user)->where('title',$note_title)->get();

            return $this->returnData('notes',$notes);
        }catch (\Exception $ex){
            return $this->returnError(500,$ex->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $user=auth('sanctum')->user();
            $note=Note::whereBelongsTo($user)->where('id',$id)->first();

            if(!$note){
                return $this->returnError(404,"الملاحظة غير موجودة!");
            }

            $note->title=$request->title;
            $note->content=$request->note_content;

            $note->save();

            return $this->returnData('note',$note,'تم التعديل بنجاح!');
        }catch (\Exception $ex){
            return $this->returnError(500,$ex->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $user=auth('sanctum')->user();
            $note=Note::whereBelongsTo($user)->where('id',$id)->first();

            if(!$note){
                return $this->returnError(404,"الملاحظة غير موجودة!");
            }

            $note->delete();

            return $this->returnSuccessMessage('تم حذف الملاحظة بنجاح!');
        }catch (\Exception $ex){
            return $this->returnError(500,$ex->getMessage());
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Note;
use App\NoteCategory;
use Auth;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;

class NotesController extends Controller
{
    public function get_categories(): JsonResponse
    {
        return response()->json([
            'categories' => NoteCategory::all(),
            'tracking' => $this->get_unread_stats(),
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store_category(Request $request,
        \App\Http\Services\UserActionLogger $userAction)
    {
        $this->validate($request, [
            'name' => 'required|unique:note_categories,name',
        ]);

        $category = new NoteCategory;
        $category->name = $request->input('name');
        $category->save();

        //Action Logger
        $userAction->logger(11, 111, $category->id, null, $category->toArray(), 'Add note category: '.$category->name);

        return $category->id;
    }

    public function update_category(Request $request,
        \App\Http\Services\UserActionLogger $userAction
    ) {
        $id = $request->input('id');
        $this->validate($request, [
            'name' => 'required|unique:note_categories,name,'.$id,
        ]);
        $category = NoteCategory::find($id);

        $current_state = $category->toArray(); //For Logging

        $category->name = $request->input('name');
        $category->save();

        //Action Logger
        $userAction->logger(11, 111, $category->id, $current_state, $category->toArray(), 'Update note category: '.$category->name);

        return $id;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete_category(Request $request,
        \App\Http\Services\UserActionLogger $userAction)
    {
        $id = $request->input('id');
        $category = NoteCategory::find($id);
        $category->delete();

        //Action Logger
        $userAction->logger(11, 111, $id, $category->toArray(), null, 'Delete category: '.$category->name);
    }

    public function get_notes_by_category(Request $request, $id): JsonResponse
    {
        $user = Auth::id();
        $notes = Note::leftJoin('note_trackings', function ($q) use ($user) {
            $q->on('notes.id', '=', 'note_trackings.note_id')
                ->where('note_trackings.user_id', '=', $user);
        })->where('category_id', $id)->select(DB::RAW('notes.*, note_trackings.id as ifNew'))->orderBy('updated_at', 'DESC')->get();

        return response()->json($notes, 200);
    }

    public function store_note(Request $request,
        \App\Http\Services\UserActionLogger $userAction)
    {
        // Log::info($request->all());
        $note = new Note;
        $note->subject = $request->input('subject');
        $note->content = $request->input('content');
        $note->category_id = $request->input('category_id');
        $note->save();

        //Action Logger
        $userAction->logger(11, null, $note->id, null, $note->toArray(), 'Add note: '.$note->subject);

        DB::table('note_trackings')->insert(['user_id' => Auth::id(), 'note_id' => $note->id]);

        return $note;
    }

    public function update_note(Request $request,
        \App\Http\Services\UserActionLogger $userAction)
    {
        // Log::info($request->all());
        $id = $request->input('id');
        $note = Note::find($id);
        $current_state = $note->toArray(); //For Logging
        $note->subject = $request->input('subject');
        $note->content = $request->input('content');
        $note->save();

        DB::table('note_trackings')->where('note_id', $id)->delete();

        //Action Logger
        $userAction->logger(11, null, $note->id, $current_state, $note->toArray(), 'Update note: '.$note->subject);

        return $note;
    }

    public function get_unread_stats()
    {
        $user = Auth::id();
        $tracking = DB::select('SELECT category_id, COUNT(*) as count FROM notes LEFT JOIN note_trackings ON notes.id = note_trackings.note_id AND note_trackings.user_id = '.$user.' WHERE note_trackings.id IS NULL GROUP BY category_id');

        return $tracking;
    }

    public function note_viewed(Request $request, $id)
    {
        DB::table('note_trackings')->insert(['user_id' => Auth::id(), 'note_id' => $id]);
    }

    public function delete_note(Request $request,
        \App\Http\Services\UserActionLogger $userAction)
    {
        $id = $request->input('id');
        $note = Note::find($id);
        $note->delete();
        DB::table('note_trackings')->where('note_id', $id)->delete();
        //Action Logger
        $userAction->logger(11, null, $note->id, $note->toArray(), null, 'Delete note: '.$note->name);
    }
}

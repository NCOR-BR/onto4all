<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\TipsRelation;
use App\Http\Requests\TipRelationStoreRequest;


class TipsRelationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $tips_relations = TipsRelation::select()->paginate(10);
        return view('tips_relations.tips_relations', compact('tips_relations'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('tips_relations.tips_relations_create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(TipRelationStoreRequest $request)
    {
        $tips_relation = TipsRelation::create($request->all());
        return redirect()->route('tips_relations.index')->with('Sucess', 'Your relation has been successfully stored')->withInput();

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $tips_relation = TipsRelation::findOrFail($id);
        return view('tips_relations.tips_relations_show', compact('tips_relation'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $tips_relation = TipsRelation::findOrFail($id);
        return view('tips_relations.tips_relations_edit', compact('tips_relation', 'id'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(TipRelationStoreRequest $request, $id)
    {
        $tips_relation = TipsRelation::findOrFail($id);
        $tips_relation->update($request->all());
        return redirect()->route('tips_relations.index')->with('Sucess', 'Your relation has been successfully updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $tips_relation = TipsRelation::findOrFail($id);
        $tips_relation->delete();
        return redirect()->back()->with('Sucess', 'Your relation has been deleted with success');
    }

    /**
     * Search's for the given name on the database
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function search(Request $request)
    {
        $tips_relations = TipsRelation::where('name','Like',  '%' .$request->search. '%')->paginate(10);
        return view('tips_relations.tips_relations', compact('tips_relations'));
    }
}

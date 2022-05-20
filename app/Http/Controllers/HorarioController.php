<?php

namespace App\Http\Controllers;
use App\Models\Regional;
use App\Models\Post;
use App\Models\Ficha;
use App\Models\Sede;
use App\Models\Ambiente;
use App\Models\Horario;
use Illuminate\Http\Request;

class HorarioController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('horario.index');
    }

    public function loadRegional($id)
    {
        $posts=Regional::find($id)->posts;
        return view('horario.select_post',compact('posts'))->render();
    }
    public function loadCentro($id)
    {
        $sedes=Post::find($id)->sedes;
        //dd(Regional::find($id)->posts);
        return view('horario.select_post',compact('sedes'))->render();
    }
    public function loadSede($id)
    {
        $fichas=Sede::find($id)->fichas;
        //dd(Regional::find($id)->posts);
        return view('horario.select_post',compact('fichas'))->render();
    }
    public function loadAmbiente($id)
    {
        $ficha=Ficha::find($id);
        $ambientes=$ficha->sede->ambientes;
        $instructores=$ficha->users()->whereHas('roles',function($q){
            $q->where('name', 'instructor');
        })->get();
        $mallas=$ficha->programa->mallas;
        $view_i=view('horario.select_post',compact('instructores'))->render();
        $view_c=view('horario.select_post',compact('mallas'))->render();
        $view_a=view('horario.select_post',compact('ambientes'))->render();
        return ["ambientes"=>$view_a,"competencias"=>$view_c,"instructores"=>$view_i];
    } 

    public function saveHorario(Request $request)
    {
        $hora_inicio = new \DateTime( $request->inicio );
        $hora_fin   = new \DateTime( $request->fin );
        $hora_fin->modify('+1 second'); // Añadimos 1 segundo para que nos muestre $hora_fin
        // Si la hora de inicio es superior a la hora fin
        // añadimos un día más a la hora fin
        if ($hora_inicio > $hora_fin) {
            $hora_fin->modify('+1 day');
            return "error-hour";
        }

        // Establecemos el intervalo en minutos
        $intervalo = new \DateInterval('PT30M');

        // Sacamos los periodos entre las horas
        $periodo   = new \DatePeriod($hora_inicio, $intervalo, $hora_fin);

        //return "".iterator_count($periodo);

        foreach($periodo as $hora){
            $hora_=$hora->format('H:i:s');
            $instructor =Horario::where("user_id",$request->instructor)->where("day",$request->day)->
            where("inicial","<",$hora_)->
            where("final",">",$hora_)->first();
            if($instructor)
                return 'error-i';

            $day=Horario::where("ambiente_id",$request->ambiente)->
                    whereTime("inicial","<",\Carbon\Carbon::parse($hora_))->where("day",$request->day)->
                    whereTime("final",">",\Carbon\Carbon::parse($hora_))->first();
                    if($day){
                    return "error";
                }
        }
        $horario=new Horario();
        $horario->user_id=$request->instructor;
        $horario->competencia_id=$request->competencia;
        $horario->inicial=$request->inicio;
        $horario->final=$request->fin;
        $horario->day=$request->day;
        $horario->ambiente_id=$request->ambiente;
        $horario->ficha_id=$request->ficha;
        $horario->save();
        $ambiente=Ambiente::find($request->ambiente);
        return view('horario.select_post',compact('ambiente'))->render();
    }
    public function editHorario($id,Request $request)
    {
        $hora_inicio = new \DateTime( $request->inicio );
$hora_fin   = new \DateTime( $request->final );
$hora_fin->modify('+1 second'); // Añadimos 1 segundo para que nos muestre $hora_fin
// Si la hora de inicio es superior a la hora fin
// añadimos un día más a la hora fin
if ($hora_inicio > $hora_fin) {
    $hora_fin->modify('+1 day');
    return "error-hour";
}

// Establecemos el intervalo en minutos
$intervalo = new \DateInterval('PT30M');

// Sacamos los periodos entre las horas
$periodo   = new \DatePeriod($hora_inicio, $intervalo, $hora_fin);
foreach($periodo as $hora){
    $hora_=$hora->format('H:i');
    $instructor =Horario::where("user_id",$request->instructor)->
            whereTime("inicial","<",\Carbon\Carbon::parse($hora_))->where("day",$request->day)->
            whereTime("final",">",\Carbon\Carbon::parse($hora_))->first();
            if($instructor)
                return "error-i";

    $day=Horario::where("ambiente_id",$request->ambiente)->
            whereTime("inicial","<",\Carbon\Carbon::parse($hora_))->where("day",$request->day)->
            whereTime("final",">",\Carbon\Carbon::parse($hora_))->first();
            if($day){
                if($day->id!=$id)
                    return "error";
        }
}
        $horario=Horario::find($id);
        $horario->inicial=$request->inicio;
        $horario->final=$request->final;

        $horario->save();
        $ambiente=Ambiente::find($request->ambiente);
        return view('horario.select_post',compact('ambiente'))->render();
    }
    public function deleteHorario($id,Request $request)
    {
        $horario= Horario::find($id);
        $horario->delete();
        $ambiente=Ambiente::find($request->ambiente);
        return view('horario.select_post',compact('ambiente'))->render();
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}

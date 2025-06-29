<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\Answer;
use App\Models\Survey;
use App\Models\Question;
use App\Models\Response;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SurveyController extends Controller
{
    public function index()
    {
        $data = Survey::all();
        return view('survey.index', compact('data'));
    }
    public function create()
    {
        return view('survey.create');
    }
    public function edit($id)
    {
        $q = Survey::findOrFail($id);
        $data['data'] = $q;

        return view('survey.edit', $data);
    }
    public function saveOrUpdate(Request $request, $id = null)
    {
        try {
            $rules = [
                'title' => 'required',
                'description' => 'required',
                'start_at' => 'nullable',
                'end_at' => 'nullable',
            ];
            if ($id != null) {


                $survey = Survey::findOrFail($id);
                $data = $request->validate($rules);

                // Cek apakah title berubah → update slug juga
                if ($data['title'] !== $survey->title) {
                    $data['slug_link'] = Survey::generateUniqueSlug($data['title']);
                }

                $survey->update($data);

                $msg = 'Survey berhasil diperbaharui';
            } else {

                $data = $request->validate($rules);
                $survey = Survey::create($data);
                $msg = 'Survey berhasil dibuat';
            }
            return redirect()->route('survey.detail', $survey->id)->with('success', $msg);
        } catch (ValidationException $e) {
            return back()->with('error', $e->errors());
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }


    public function destroy(Request $request)
    {
        try {
            Survey::destroy($request->input('id'));
            return back()->with('success', 'Survey deleted successfully');
        } catch (\Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }
    public function detail($id)
    {
        $survey = Survey::findOrFail($id);
        return view('survey.detail', compact('survey'));
    }

    public function surveyPublic($slug)
    {
        $now = Carbon::now();
        $survey = Survey::where('slug_link', $slug)
            ->where('status', '1')
            ->whereDate('start_at', '<=', $now)
            ->whereDate('end_at', '>=', $now)
            ->firstOrFail();

        return view('survey.public', compact('survey'));
    }
    public function submitSurvey(Request $request, $slug)
    {
        DB::beginTransaction();
        try {

            // dd($request->input());
            // Ambil survey berdasarkan slug
            $now = Carbon::now();
            // dd($slug);
            $survey = Survey::where('slug_link', $slug)
                ->where('status', '1')
                ->whereDate('start_at', '<=', $now)
                ->whereDate('end_at', '>=', $now)
                ->first();

            if (!$survey) {
                throw new Exception('Survey not found');
            }


            // Ambil semua pertanyaan terkait dengan survey ini
            $questions = Question::where('survey_id', $survey->id)->get();

            // Inisialisasi array untuk validasi
            $validationRules = [];





            // dd($response->id);

            // Bangun aturan validasi dinamis
            // foreach ($questions as $question) {
            //     if ($question->is_required) {
            //         // Tentukan aturan validasi berdasarkan tipe pertanyaan
            //         if ($question->type == 'checkbox') {
            //             // Untuk checkbox, pastikan ada minimal satu pilihan
            //             $validationRules["answere_{$question->id}"] = 'required|array|min:1';
            //         } elseif ($question->type == 'radio' || $question->type == 'dropdown') {
            //             // Untuk radio dan dropdown, pastikan ada satu pilihan
            //             $validationRules["answere_{$question->id}"] = 'required|string';
            //         } elseif ($question->type == 'rate') {
            //             // Untuk rating, pastikan ada angka (misalnya angka 1 hingga 5)
            //             $validationRules["answere_{$question->id}"] = 'required|integer|min:1';
            //         } elseif ($question->type == 'text') {
            //             // Untuk input text, pastikan ada nilai dan validasi panjang minimal jika diperlukan
            //             $validationRules["answere_{$question->id}"] = 'required|string';
            //         } elseif ($question->type == 'email') {
            //             // Validasi email
            //             $validationRules["answere_{$question->id}"] = 'required|email';
            //         } elseif ($question->type == 'date') {
            //             // Validasi tanggal
            //             $validationRules["answere_{$question->id}"] = 'required|date';
            //         } elseif ($question->type == 'month') {
            //             // Validasi bulan
            //             $validationRules["answere_{$question->id}"] = 'required|date_format:Y-m';
            //         } elseif ($question->type == 'datetime-local') {
            //             // Validasi tanggal dan waktu
            //             $validationRules["answere_{$question->id}"] = 'required|date';
            //         } elseif ($question->type == 'textarea') {
            //             // Validasi untuk textarea
            //             $validationRules["answere_{$question->id}"] = 'required|string';
            //         }
            //     }
            // }


            foreach ($questions as $question) {
                $key = "answere_{$question->id}";

                // Required rules
                if ($question->is_required) {
                    switch ($question->type) {
                        case 'checkbox':
                            $validationRules[$key] = ['required', 'array', 'min:1'];
                            break;
                        case 'radio':
                        case 'dropdown':
                            $validationRules[$key] = ['required', 'string'];
                            break;
                        case 'rate':
                            $validationRules[$key] = ['required', 'integer', 'min:1'];
                            break;
                        case 'text':
                        case 'textarea':
                            $validationRules[$key] = ['required', 'string'];
                            break;
                        case 'email':
                            $validationRules[$key] = ['required', 'email'];
                            break;
                        case 'date':
                            $validationRules[$key] = ['required', 'date'];
                            break;
                        case 'month':
                            $validationRules[$key] = ['required', 'date_format:Y-m'];
                            break;
                        case 'datetime-local':
                            $validationRules[$key] = ['required', 'date'];
                            break;
                        default:
                            $validationRules[$key] = ['nullable'];
                            break;
                    }
                } else {
                    $validationRules[$key] = ['nullable'];
                }

                // Unique per survey
                if ($question->is_unique) {
                    $validationRules[$key][] = function ($attribute, $value, $fail) use ($survey) {
                        $exists = DB::table('answer')
                            ->join('response', 'answer.response_id', '=', 'response.id')
                            ->where('response.survey_id', $survey->id)
                            ->where('answer.answer_text', $value)
                            ->exists();

                        if ($exists) {
                            $fail('This answer has already been submitted for this survey.');
                        }
                    };
                }
            }



            $validator = Validator::make($request->all(), $validationRules);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $response =  Response::createFromSurvey($survey);

            foreach ($questions as $question) {
                $key = 'answere_' . $question->id;
                $answer = $request->input($key);


                if (is_array($answer)) {
                    foreach ($answer as $checkboxAnswer) {
                        Answer::create([
                            'response_id' => $response->id,
                            'question_id' => $question->id,
                            'answer_text' => $checkboxAnswer,
                        ]);
                    }
                } else {
                    $d = Answer::create([
                        'response_id' => $response->id,
                        'question_id' => $question->id,
                        'answer_text' => $answer !== null ? trim(strip_tags($answer)) : null,
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('survey.thankyou', $survey->slug_link); // Redirect setelah submit
        } catch (Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', $e->getMessage())
                ->withInput();
        }
    }
    public function thankyou($slug)
    {
        $survey = Survey::where('slug_link', $slug)->first();
        return view('survey.thankyou', compact('survey'));
    }
    public function toggleStatus(Request $request, $id)
    {
        $survey = Survey::findOrFail($id);
        $survey->status = $request->input('status'); // pakai status dari client
        $survey->save();

        return response()->json([
            'status' => $survey->status,
            'message' => 'Status survey berhasil diubah.'
        ]);
    }
}

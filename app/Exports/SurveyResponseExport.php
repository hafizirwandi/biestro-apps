<?php

namespace App\Exports;

use App\Models\Response;
use App\Models\Question;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SurveyResponseExport implements FromArray, WithHeadings
{
    protected $survey;

    public function __construct($survey)
    {
        $this->survey = $survey;
    }

    public function headings(): array
    {
        $questions = Question::where('survey_id', $this->survey->id)
            ->orderBy('position')
            ->pluck('question_text')
            ->toArray();

        return array_merge(['Submitted Time'], $questions);
    }

    public function array(): array
    {
        $questions = Question::where('survey_id', $this->survey->id)
            ->orderBy('position')
            ->get();

        $responses = Response::where('survey_id', $this->survey->id)->get();

        $data = [];

        foreach ($responses as $response) {
            $row = [];
            $row[] = $response->created_at->format('Y-m-d H:i:s');
            foreach ($questions as $question) {
                // Ambil semua jawaban yang sesuai dengan question_id
                $answers = $response->answer()
                    ->where('question_id', $question->id)
                    ->get();

                // Cek apakah tipe pertanyaannya checkbox
                if ($question->type == 'checkbox') {
                    $texts = $answers->pluck('answer_text')->filter()->toArray();
                    $row[] = implode(', ', $texts);
                } else {
                    $answer = $answers->first();
                    $row[] = $answer ? $answer->answer_text : '';
                }
            }
            $data[] = $row;
        }
        return $data;
    }
}

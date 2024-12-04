<?php
namespace App\Http\Controllers;

use App\Services\Image\ImageOptimizer;
use App\Config\ImageOptimizerConfig;
use Illuminate\Http\Request;
use App\Exceptions\ImageOptimizerException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;


class ImageController extends Controller
{
    public function __construct(
        private readonly ImageOptimizerConfig $config
    ) {}

    public function optimize(Request $request, ImageOptimizer $optimizer)
    {
        $request->validate([
            'image' => [
                'required',
                'file',
                'image',
                'max:' . $this->config->uploadLimits['max_file_size']
            ],
            'format' => [
                'required',
                'in:' . implode(',', $this->config->supportedFormats)
            ],
        ]);
        try {
            $uploadedFile = $request->file('image');
            $inputPath = $uploadedFile->store($this->config->paths['uploads']);

            $outputPath = $this->config->paths['optimized'] . '/' .
                Str::uuid() . '.' .
                $request->input('format');

            $optimizer->optimize(
                inputPath: $inputPath,
                outputPath: $outputPath,
                outputFormat: $request->input('format')
            );

            return response()->json([
                'url' => Storage::url($outputPath)
            ]);

        } catch (ImageOptimizerException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}

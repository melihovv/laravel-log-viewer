<?php

namespace Melihovv\LaravelLogViewer;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;
use InvalidArgumentException;

class Controller
{
    public function index(Request $request)
    {
        $viewer = app('log-viewer');
        $error = false;

        try {
            if ($request->has('file')) {
                $viewer->setCurrentFile(base64_decode($request->get('file')));

                if ($request->has('download')) {
                    return Response::download($viewer->getCurrentFile());
                }

                if ($request->has('delete')) {
                    File::delete($viewer->getCurrentFile());

                    return Response::redirectTo($request->url());
                }
            } elseif ($request->has('dir')) {
                $viewer->setCurrentDirectory(base64_decode($request->get('dir')));
            }
        } catch (InvalidArgumentException $e) {
            $error = true;
        }

        return View::make('log-viewer::log', [
            'logs' => $viewer->getLogsFromCurrentFile(),
            'dirItems' => $viewer->getCurrentDirectoryContent(),
            'currentFile' => $viewer->getCurrentFileRelativeToBaseDir(),
            'parentDirPath' => $viewer->getRelativePathToCurrentDirectoryParent(),
            'isCurrentDirectoryBase' => $viewer->isCurrentDirectoryBase(),
            'parentDirs' => $viewer->getParentDirectories(),
            'error' => $error,
        ]);
    }
}

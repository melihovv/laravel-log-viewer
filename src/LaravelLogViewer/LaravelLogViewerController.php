<?php

namespace Melihovv\LaravelLogViewer;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;

class LaravelLogViewerController
{
    public function index()
    {
        $viewer = app()->make('log-viewer');

        if (Request::has('file')) {
            $viewer->setCurrentFile(base64_decode(Request::get('file')));

            if (Request::has('download')) {
                return Response::download($viewer->getCurrentFile());
            } elseif (Request::has('delete')) {
                File::delete($viewer->getCurrentFile());

                return app()->make('redirect')->to(Request::url());
            }
        } elseif (Request::has('dir')) {
            $viewer->setCurrentDirectory(base64_decode(Request::get('dir')));
        }

        return app('view')->make('laravel-log-viewer::log', [
            'logs' => $viewer->getLogsFromCurrentFile(),
            'dirItems' => $viewer->getCurrentDirectoryContent(),
            'currentFile' => $viewer->getCurrentFileRelativeToBaseDir(),
            'parentDirPath' =>
                $viewer->getRelativePathToCurrentDirectoryParent(),
            'isCurrentDirectoryBase' => $viewer->isCurrentDirectoryBase(),
        ]);
    }
}

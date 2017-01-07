<?php

namespace Melihovv\LaravelLogViewer;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;

class LaravelLogViewerController
{
    public function index()
    {
        if (Request::has('file')) {
            \LogViewer::setCurrentFile(base64_decode(Request::get('file')));

            if (Request::has('download')) {
                return Response::download(\LogViewer::getCurrentFile());
            } elseif (Request::has('delete')) {
                File::delete(\LogViewer::getCurrentFile());

                return app()->make('redirect')->to(Request::url());
            }
        } elseif (Request::has('dir')) {
            \LogViewer::setCurrentDirectory(base64_decode(Request::get('dir')));
        }

        return app('view')->make('laravel-log-viewer::log', [
            'logs' => \LogViewer::getLogsFromCurrentFile(),
            'dirItems' => \LogViewer::getCurrentDirectoryContent(),
            'currentFile' => \LogViewer::getCurrentFileRelativeToBaseDir(),
            'parentDirPath' =>
                \LogViewer::getRelativePathToCurrentDirectoryParent(),
            'isCurrentDirectoryBase' => \LogViewer::isCurrentDirectoryBase(),
        ]);
    }
}

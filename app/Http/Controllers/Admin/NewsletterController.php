<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Yajra\DataTables\Facades\DataTables;

class NewsletterController extends Controller
{
    public function index()
    {
        return view('admin.newsletter.index');
    }

    public function data()
    {
        $subscribers = NewsletterSubscriber::select('newsletter_subscribers.*');

        return DataTables::of($subscribers)
            ->editColumn('created_at', fn ($s) => $s->created_at->format('d M Y, h:i A'))
            ->addColumn('actions', function ($s) {
                return '<form method="POST" action="' . route('admin.newsletter.destroy', $s) . '">
                    ' . csrf_field() . method_field('DELETE') . '
                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm(\'Delete this subscriber?\')">Delete</button>
                </form>';
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    public function destroy(NewsletterSubscriber $subscriber)
    {
        $subscriber->delete();
        return back()->with('success', 'Subscriber removed.');
    }

    public function export()
    {
        $subscribers = NewsletterSubscriber::orderBy('created_at')->get();

        $csv = "Email,Subscribed On\n";
        foreach ($subscribers as $s) {
            $csv .= '"' . $s->email . '","' . $s->created_at->format('Y-m-d H:i:s') . '"' . "\n";
        }

        return Response::make($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="newsletter-subscribers.csv"',
        ]);
    }
}
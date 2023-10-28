<?php

namespace App\Http\Controllers\Data;

use App\Http\Controllers\Controller;

//return type View
use Illuminate\View\View;
use Illuminate\Http\Request;

//import Model "Post
use App\Models\Post;

//return type redirectResponse
use Illuminate\Http\RedirectResponse;
//import Facade "Storage"
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    // Method Tampilkan data
    public function index(): View
    {
        //get posts
        $posts = Post::latest()->paginate(5);

        //render view with posts
        return view('posts.index', compact('posts'));
    }

    // Method tampil form inputan
    public function create(): View
    {
        return view('posts.create');
    }

    // Method proses data input ke database
    public function store(Request $request): RedirectResponse
    {
        //validate form
        $this->validate($request, [
            'image'     => 'required|image|mimes:jpeg,jpg,png|max:2048',
            'nama'     => 'required',
            'nim'   => 'required',
            'jurusan' => 'required',
            'alamat'   => 'required'
        ]);

        //upload image
        $image = $request->file('image');
        $image->storeAs('public/posts', $image->hashName());

        //create post
        Post::create([
            'image'     => $image->hashName(),
            'nama'     => $request->nama,
            'nim'     => $request->nim,
            'jurusan'     => $request->jurusan,
            'alamat'   => $request->alamat
        ]);

        //redirect to index
        return redirect()->route('posts.index')->with(['success' => 'Data Sukses Disimpan!']);
    }

    // Method Ubah
    public function edit(string $id): View
    {
        $post = Post::findOrFail($id);
        return view('posts.edit', compact('post'));
    }

    // Method Proses Update Data
    public function update(Request $request, $id): RedirectResponse
    {
        $this->validate($request, [
            'image'     => 'image|mimes:jpeg,jpg,png|max:2048',
            'nama'     => 'required',
            'nim'   => 'required',
            'jurusan' => 'required',
            'alamat'   => 'required'
        ]);

        $post = Post::findOrFail($id);
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $image->storeAs('public/posts', $image->hashName());
            Storage::delete('public/posts/'.$post->image);
            $post->update([
                'image'     => $image->hashName(),
                'nama'     => $request->nama,
                'nim'     => $request->nim,
                'jurusan'     => $request->jurusan,
                'alamat'   => $request->alamat
            ]);
        } else {
            $post->update([
                'nama'     => $request->nama,
                'nim'     => $request->nim,
                'jurusan'     => $request->jurusan,
                'alamat'   => $request->alamat
            ]);
        }
        return redirect()->route('posts.index')->with(['success' => 'Data Berhasil Diubah!']);
    }

    // Method Hapus
    public function destroy($id): RedirectResponse
    {
        $post = Post::findOrFail($id);
        
        Storage::delete('public/posts/'. $post->image);

        $post->delete();

        return redirect()->route('posts.index')->with(['success' => 'Data Berhasil Dihapus!']);
    }

    // Method detail
    public function show(string $id): View
    {
        $post = Post::findOrFail($id);

        return view('posts.show', compact('post'));
    }

}


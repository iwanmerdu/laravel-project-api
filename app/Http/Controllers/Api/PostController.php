<?php

namespace App\Http\Controllers\Api;

//import Model "Post"
use App\Models\Post;
//import Resource "PostResource"
use App\Http\Resources\PostResource;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

//import Facade "Validator"
use Illuminate\Support\Facades\Validator;
//import Facade "Storage"
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    // Method index
    public function index()
    {
        //tampilkan semua data di tabel posts
        $posts = Post::latest()->paginate(5);

        //munculkan data sesuai resource di tabel post
        return new PostResource(true, 'List Data Posts', $posts);
    }

    // Method untuk Simpan Data
    public function store(Request $request)
    {
        //Definisikan validasi data
        $validator = Validator::make($request->all(), [
            'nama' => 'required',
            'nim' => 'required',
            'image'     => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'jurusan'     => 'required',
            'alamat'   => 'required',
        ]);

        //Mengecek apakah data sudah valid
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //upload gambar
        $image = $request->file('image');
        $image->storeAs('public/posts', $image->hashName());

        //kirimkan ke tabel post
        $post = Post::create([
            'nama' => $request->nama,
            'nim' => $request->nim,
            'image'     => $image->hashName(),
            'jurusan'     => $request->jurusan,
            'alamat'   => $request->alamat,
        ]);

        //Tampilkan respon dari Server
        return new PostResource(true, 'Data Berhasil Ditambahkan!', $post);
    }

    // Method Detail Data
    public function show($id)
    {
        //Temukan data di tabel post sesuai ID
        $post = Post::find($id);

        //return menggunakan API Resource
        return new PostResource(true, 'Detail Data', $post);
    }

    // Method Ubah Data
    public function update(Request $request, $id)
    {
        //tentukan validasi data yag diubah
        $validator = Validator::make($request->all(), [
            'nama'     => 'required',
            'nim'   => 'required',
            'jurusan'   => 'required',
            'alamat'   => 'required',
        ]);

        //cek Pesan kesalahan
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //Temukan data di tabel post sesuai ID
        $post = Post::find($id);

        //Cek apakah gambar diubah atau tidak
        if ($request->hasFile('image')) {

            //upload gambar
            $image = $request->file('image');
            $image->storeAs('public/posts', $image->hashName());

            //Hapus gambar lama
            Storage::delete('public/posts/'.basename($post->image));

            //Ubah dengan gambar baru
            $post->update([
                'nama'     => $request->nama,
                'nim'     => $request->nim,
                'image'     => $image->hashName(),
                'jurusan'     => $request->jurusan,
                'alamat'   => $request->alamat,
            ]);

        } else {

            //Ubah apabila tidak ada gambar baru
            $post->update([
                'nama'     => $request->nama,
                'nim'   => $request->nim,
                'jurusan'     => $request->jurusan,
                'alamat'   => $request->alamat,
            ]);
        }

        //tampilkan respon
        return new PostResource(true, 'Data Berhasil Diubah!', $post);
    }

    // Method Hapus Data
    public function destroy($id)
    {
        //Cari data ditabel berdasarkan ID
        $post = Post::find($id);

        //Hapus Gambar di Storage
        Storage::delete('public/posts/'.basename($post->image));

        //Hapus data di tabel
        $post->delete();

        //Berikan Respon berhasil
        return new PostResource(true, 'Data Berhasil Dihapus!', null);
    }


}

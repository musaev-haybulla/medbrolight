<!DOCTYPE html>
<html>
<head>
    <title>Image Optimizer Test</title>
</head>
<body>
    <form action="{{ route('image.optimize') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div>
            <label>Select image:</label>
            <input type="file" name="image" accept="image/*">
        </div>
        
        <div>
            <label>Output format:</label>
            <select name="format">
                <option value="webp">WebP</option>
                <option value="jpg">JPG</option>
                <option value="heic">HEIC</option>
            </select>
        </div>

        <button type="submit">Optimize</button>
    </form>

    @if(session('error'))
        <div style="color: red">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div style="color: red">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
</body>
</html>
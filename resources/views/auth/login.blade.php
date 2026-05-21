@extends('layouts.app')

@section('content')

<style>
    *{
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body{
        font-family: 'Segoe UI', sans-serif;
        background: linear-gradient(135deg, #667eea, #764ba2);
        min-height: 100vh;
    }

    .login-wrapper{
        width: 100%;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .login-card{
        width: 100%;
        max-width: 420px;
        background: #fff;
        border-radius: 18px;
        padding: 40px;
        box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        animation: fadeIn 0.4s ease;
    }

    .login-card h2{
        text-align: center;
        margin-bottom: 10px;
        color: #222;
        font-size: 32px;
        font-weight: 700;
    }

    .login-subtitle{
        text-align: center;
        color: #777;
        margin-bottom: 30px;
        font-size: 14px;
    }

    .form-group{
        margin-bottom: 20px;
    }

    .form-group label{
        display: block;
        margin-bottom: 8px;
        font-size: 14px;
        font-weight: 600;
        color: #444;
    }

    .form-control{
        width: 100%;
        height: 52px;
        border: 1px solid #ddd;
        border-radius: 12px;
        padding: 0 15px;
        font-size: 15px;
        transition: 0.3s ease;
        outline: none;
    }

    .form-control:focus{
        border-color: #667eea;
        box-shadow: 0 0 0 4px rgba(102,126,234,0.15);
    }

    .btn-login{
        width: 100%;
        height: 52px;
        border: none;
        border-radius: 12px;
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: #fff;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: 0.3s ease;
    }

    .btn-login:hover{
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(102,126,234,0.25);
    }

    .alert-error{
        background: #ffe5e5;
        color: #d63031;
        padding: 15px;
        border-radius: 10px;
        margin-bottom: 20px;
        font-size: 14px;
    }

    .bottom-text{
        text-align: center;
        margin-top: 20px;
        color: #666;
        font-size: 14px;
    }

    .bottom-text a{
        color: #667eea;
        text-decoration: none;
        font-weight: 600;
    }

    @keyframes fadeIn{
        from{
            opacity: 0;
            transform: translateY(20px);
        }
        to{
            opacity: 1;
            transform: translateY(0);
        }
    }

    @media(max-width: 480px){
        .login-card{
            padding: 30px 20px;
        }
    }
</style>

<div class="login-wrapper">
    <div class="login-card">

        <h2>Welcome Back</h2>
        <p class="login-subtitle">
            Login to continue to your dashboard
        </p>

        @if($errors->any())
            <div class="alert-error">
                <ul style="padding-left:20px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="form-group">
                <label>Email Address</label>
                <input 
                    type="email" 
                    name="email" 
                    class="form-control" 
                    value="{{ old('email') }}" 
                    placeholder="Enter your email"
                    required 
                    autofocus
                >
            </div>

            <div class="form-group">
                <label>Password</label>
                <input 
                    type="password" 
                    name="password" 
                    class="form-control"
                    placeholder="Enter your password"
                    required
                >
            </div>

            <button type="submit" class="btn-login">
                Login
            </button>
        </form>

        <div class="bottom-text">
            Don't have an account?
            <a href="#">Register</a>
        </div>

    </div>
</div>

@endsection
@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/contact.css')}}">
@endsection
    
@section('content')
<div class="contact-form">
    <h2 class="contact-form__heading content__heading">Contact</h2>
    <div class="contact-form__inner">
        <form action="confirm" method="post">
            @csrf
            <div class="contact-form__group contact-form__name-group">
                <label class="contact-form__label" for="name">お名前</label>
                <div class="contact-form__name-inputs">
                    <input class="contact-form__input contact-form__name-input" type="text" name="first_name" id="name" value="{{ old('first_name') }}" placeholder="例：山田">
                    <input class="contact-form__input contact-form__name-input" type="text" name="last_name" id="name" value="{{ old('last_name') }}" placeholder="例：太郎">
                </div>
                <div class="contact-form__error">
                    @if ($errors->has('first_name'))
                    <p class="contact-form__error-message-message-first-name">
                        {{$errors->first('first_name')}}
                    </p>
                    @endif
                    @if ($errors->has('last_name'))
                    <p class="contact-form__error-message-message-last-name">
                        {{$errors->first('last_name')}}
                    </p>
                    @endif
                </div>
            </div>

            <div class="contact-form__group">
                <label class="contact-form__label">性別</label>
                <div class="contact-form__gender-inputs">
                    <div class="contact-form__gender-option">
                        <label class="contact-form__gender-label">
                            <input class="contact-form__gender-input" type="radio" name="gender" id="male" value="1"
                            {{ old('gender') == 1 || old('gender') === null ? 'checked' : '' }}>
                            <span class="contact-form__gender-text">男性</span>
                        </label>
                    </div>

                    <div class="contact-form__gender-option">
                        <label class="contact-form__gender-label">
                            <input class="contact-form__gender-input" type="radio" name="gender" id="female" value="2"
                            {{ old('gender') == 2 ? 'checked' : '' }}>
                            <span class="contact-form__gender-text">女性</span>
                        </label>
                    </div>

                    <div class="contact-form__gender-option">
                        <label class="contact-form__gender-label">
                            <input class="contact-form__gender-input" type="radio" name="gender" id="other" value="3"
                            {{ old('gender') == 3 ? 'checked' : '' }}>
                            <span class="contact-form__gender-text">その他</span>
                        </label>
                    </div>
                </div>
                <p class="contact-formerror">
                    @error('gender')
                    {{ $message }}
                    @enderror
                </p>
            </div>

            <div class="contact-form__group">
                <label class="contact-form__label" for="email">メールアドレス</label>
                <input class="contact-form__input" type="email" name="email" id="email" value="{{ old('email') }}" placeholder="例：test@example.com">
                <p class="contact-formerror">
                    @error('email')
                    {{ $message }}
                    @enderror
                </p>
            </div>

            <div class="contact-form__group">
                <label class="contact-form__label" for="tel">電話番号</label>
                <div class="contact-form__tel-inputs">
                    <input class="contact-form__input contact-form__tel-input" type="tel" name="tel_1" id="tel"
                    value="{{ old('tel_1') }}">
                    <span>-</span>

                    <input class="contact-form__input contact-form__tel-input" type="tel" name="tel_2" value="{{ old('tel_2') }}">
                    <span>-</span>

                    <input class="contact-form__input contact-form__tel-input" type="tel" name="tel_3" value="{{ old('tel_3') }}">
                </div>
                <p class="contact-formerror">
                     @if ($errors->has('tel_1'))
                     {{$errors->first('tel_1')}}
                     @elseif ($errors->has('tel_2'))
                     {{$errors->first('tel_2')}}
                     @elseif ($errors->has('tel_3'))
                     {{$errors->first('tel_3')}}
                     @endif
                </p>
            </div>

            <div class="contact-form__group">
                <label class="contact-form__label" for="address">住所</label>
                    <input class="contact-form__input" type="text" name="address" id="address" value="{{ old ('address') }}" placeholder="例：東京都渋谷区千駄ヶ谷1-2-3">
                <p class="contact-formerror">
                    @error('address')
                    {{ $message }}
                    @enderror
                </p>
            </div>

            <div class="contact-form__group">
                <label class="contact-form__label" for="building">建物名</label>
                <input class="contact-form__input" type="text" name="building" id="building" value="{{ old('building') }}"
                placeholder="例：千駄ヶ谷マンション101">
            </div>


            <div class="contact-form__group">
                <label class="contact-form__label">お問い合わせの種類</label>
                <div class="contact-form__select-inner">
                    <select class="contact-form__select" name="category_id" id="category_id">
                        <option disabled selected>選択してください</option>
                        @foreach($categories as $category)
                         <option value="{{ $category->id }}" {{ old('category_id')==$category->id ? 'selected' : '' }}>{{
                         $category->content }}</option>
                        @endforeach
                    </select>
                </div>
                <p class="contact-formerror">
                    @error('category_id')
                    {{ $message }}
                    @enderror
                </p>
            </div>

            <div class="contact-form__group">
                <label class="contact-form__label" for="detail">お問い合わせ内容</label>
                <textarea class="contact-form__textarea" name="detail" id="" cols="30" rows="10" placeholder="お問い合わせ内容をご記載ください">{{ old('detail') }}</textarea>
                <p class="contact-formerror">
                    @error('detail')
                    {{ $message }}
                    @enderror
                </p>
            </div>
            <input class="contact-form__btn btn" type="submit" value="確認画面">
        </form>
    </div>
</div>
@endsection

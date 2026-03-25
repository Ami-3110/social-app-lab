# Social App Lab

Laravel（API）とNuxt（SPA）で構築したTwitter風のSNSアプリです。

---

## 概要

本アプリは、投稿・コメント・いいね・ブックマーク・リポスト・通知といったSNSの基本機能を備えたフルスタックアプリケーションです。

API設計やリレーション構造を意識して実装しています。

---

## 使用技術

### バックエンド
- Laravel 11
- Laravel Sanctum（認証）
- PostgreSQL / SQLite

### フロントエンド
- Nuxt 4
- TypeScript
- Tailwind CSS

### インフラ
- Vercel（フロントエンド）
- Railway（API・DB）

---

## 主な機能

- 投稿・コメント機能（スレッド対応）
- いいね・ブックマーク・リポスト
- フォロー機能
- 画像投稿（投稿・コメント両対応）
- ユーザープロフィール（投稿 / コメント / いいね / メディア）
- 検索機能（投稿・ユーザー・トピック）
- 通知機能

---

## 通知機能

- 通知一覧の取得（ページネーション対応）
- 未読通知数の取得
- 通知の一括既読化
- 通知に関連するデータ（actor / post / comment）を含めて取得

---

## ER図

以下のER図でデータ構造を整理しています。

- `er-main.svg`
- `er-notifications.svg`

---

## セットアップ手順

### バックエンド

```bash
cd api
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
```

### フロントエンド
```bash
cd web
npm install
npm run dev
```

---

## デプロイ
- フロントエンド：Vercel
- バックエンド：Railway

## 工夫した点
- フロントエンドとバックエンドを分離した構成で設計
- 通知機能において、actor / post / comment のリレーションを整理
- ページネーションや未読管理など、実務に近い仕様を意識
- メディア投稿やスレッド構造など、SNSとしての基本機能を網羅

## 今後の改善案
- パフォーマンス最適化
- UI/UXの改善
- テストの拡充
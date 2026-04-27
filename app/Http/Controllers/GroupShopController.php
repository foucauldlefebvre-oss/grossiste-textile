<?php

namespace App\Http\Controllers;

use App\Models\GroupShop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class GroupShopController extends Controller
{
    public function show(GroupShop $groupShop)
    {
        if (! $groupShop->isOpen()) {
            abort(404);
        }

        // Check password gate
        if ($groupShop->password && ! session("group_shop_access.{$groupShop->id}")) {
            return view('group-shop.password', compact('groupShop'));
        }

        $groupShop->load(['products.product.colors', 'products.product.sizes', 'products.technique']);

        return view('group-shop.show', compact('groupShop'));
    }

    public function authenticate(Request $request, GroupShop $groupShop)
    {
        $request->validate(['password' => 'required|string']);

        if (! Hash::check($request->password, $groupShop->password)) {
            return back()->withErrors(['password' => 'Mot de passe incorrect.']);
        }

        session(["group_shop_access.{$groupShop->id}" => true]);

        return redirect()->route('group-shop.show', $groupShop);
    }

    public function confirmation(GroupShop $groupShop)
    {
        if (! $groupShop->isOpen()) {
            abort(404);
        }

        return view('group-shop.confirmation', compact('groupShop'));
    }
}

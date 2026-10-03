<?php

function statusBadge($status)
{
    if ($status) {
        return '<span class="inline-flex items-center rounded-md bg-indigo-50 px-2 py-1 text-xs font-medium text-indigo-700">Active</span>';
    }

    return '<span class="inline-flex items-center rounded-md bg-red-50 px-2 py-1 text-xs font-medium text-red-700">Inactive</span>';
}